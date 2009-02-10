<?php
/**
 * @package Bambus
 * @subpackage Contents
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 17.10.2008
 * @license GNU General Public License 3
 * @todo better config 
 */
class CFeed extends BContent implements ISupportsSidebar, IGlobalUniqueId, IGeneratesFeed 
{
    const GUID = 'org.bambuscms.content.cfeed';
    
    public function getClassGUID()
    {
        return self::GUID;
    }
    const CLASS_NAME = 'CFeed';
    
    const ALL = 'All';
    const MATCH_SOME = 'MatchSome';
    const MATCH_ALL = 'MatchAll';
    const MATCH_NONE = 'MatchNone';
    
    const HEADER = 0;
    const ITEM = 1;
    const FOOTER = 2;
    const SETTINGS = 3;
    const HEADER_AND_FOOTER = 0;
    
    const PREFIX = 0;
    const SUFFIX = 1;
    
    const OPTIONS = 2;
    const ORDER = 1;
    const CAPTIONS = 0; 
    
    private $_contentLoaded = false;
    /**
     * @var DSQLResult
     */
    private $FeedDBRes = null;
    /**
     * @var SComponentIndex
     */
    private $SCI;
    
    private $_data = array(
        self::CAPTIONS => array(
            self::HEADER => array(
                'NumberOfEnd' => array('',''),
                'NumberOfStart' => array('',''),
                'FoundItems' => array('Found: ',' Items'),
                'Link' => array('Previous page','Next page'),
                'Pagina' => array('Page: ','')
            ),
            self::ITEM => array(
                'Link' => array('',''),
                'NoItemsFound' => array('','')
            ),
            self::FOOTER => array(
                'NumberOfEnd' => array('',''),
                'NumberOfStart' => array('',''),
                'FoundItems' => array('Found: ',' Items'),
                'Link' => array('Previous page','Next page'),
                'Pagina' => array('Page: ','')
            )
        ),
        self::ORDER => array(
            self::HEADER => array(
                'PrevLink' => 1,
                'Pagina' => 2,
                'NextLink' => 3,
                'NumberOfStart' => null,
                'NumberOfEnd' => null,
                'FoundItems' => null
            ),
            self::ITEM => array(
                'Title' => 1,
                'Description' => 2,
                'Author' => 3,
                'PubDate' => 4,
                'Content' => null,
                'Link' => null,
                'Tags' => null,
            	'ModDate' => null,
                'Icon' => null,
                'PreviewImage' => null
            ),
            self::FOOTER => array(
                'PrevLink' => 1,
                'NextLink' => 2,
                'FoundItems' => 3,
                'Pagina' => null,
                'NumberOfStart' => null,
                'NumberOfEnd' => null
            )
        ),
        self::OPTIONS => array(
            self::HEADER => array(
                'PaginaType' => true
			),
            self::FOOTER => array(
                'PaginaType' => true
			),
			self::ITEM => array(
                'ModDateFormat' => 'c',
                'PubDateFormat' => 'c',
                'LinkTitle' => true,
                'LinkTags' => false,
                'IconSize' => 48,
			    'PreviewImageWidth' => 100,
			    'PreviewImageHeight' => 100,
			    'PreviewImageMode' => '1c',//force crop
			    'PreviewImageBgColor' => '#ffffff'
			),
            self::SETTINGS => array(
                'ItemsPerPage' => 10,
                'MaxPages' => 1000,
                'Filter' => array(),
                'FilterMethod' => CFeed::ALL,
                'TargetView' => '',
                'SortOrder' => true,
                'SortBy' => 'title'
            )
        )
    );
    
    private function _getConfVal($type, $target, $key)
    {
        if(!isset($this->_data[$type]) || !isset($this->_data[$type][$target]) || !isset($this->_data[$type][$target][$key]))
        {
            throw new XArgumentException(sprintf('key /%s/%s/%s not found', $type, $target, $key));
        }
        return $this->_data[$type][$target][$key];
    }
    
    //captions
    public function changeCaption($forType, $andKey, $toPrefix, $andSuffix)
    {
        
        if(!isset($this->_data[self::CAPTIONS][$forType]) || !isset($this->_data[self::CAPTIONS][$forType][$andKey]))
        {
            throw new XArgumentException(sprintf('key /captions/%s/%s not found', $forType, $andKey));
        }
        $this->_modified = true;
        $this->_data[self::CAPTIONS][$forType][$andKey][self::PREFIX] = $toPrefix;
        $this->_data[self::CAPTIONS][$forType][$andKey][self::SUFFIX] = $andSuffix;
    }
    
    public function caption($forType, $andKey, $item = null)
    {
        if(!isset($this->_data[self::CAPTIONS][$forType]) || !isset($this->_data[self::CAPTIONS][$forType][$andKey]))
        {
            throw new XArgumentException(sprintf('key /captions/%s/%s not found', $forType, $andKey));
        }
        if($item === null && ($item == 0 || $item == 1))
        {
            return $this->_data[self::CAPTIONS][$forType][$andKey];
        }
        else
        {
             return $this->_data[self::CAPTIONS][$forType][$andKey][$item];
        }
    }
    
    //order 1..n - unused are null
	public function changeOrder($forType, array $toData)
	{
	    $this->_modified = true;
	    //right key
        if(!isset($this->_data[self::ORDER][$forType]))
        {
            throw new XArgumentException(sprintf('key /order/%s not found', $forType));
        }	    
        //set to whatever data is given
        foreach ($this->_data[self::ORDER][$forType] as $key => $pos) 
	    {
	    	$this->_data[self::ORDER][$forType][$key] = (isset($toData[$key]))
	    	     ? $toData[$key]
	    	     : null;
	    }
	    //order by given data
	    asort($this->_data[self::ORDER][$forType]);
	    //change data to correct numbers (1..n or null) 
	    $i = 0;
	    $ordered = array();
	    $nulled = array();
	    foreach ($this->_data[self::ORDER][$forType] as $key => $data) 
	    {
	        if($data == null)
	        {
	            $nulled[$key] = null;
	        }
	        else
	        {
	            $ordered[$key] = ++$i;
	        }
	    }
	    foreach ($nulled as $k => $n) 
	    {
	    	$ordered[$k] = null;
	    }
	    $this->_data[self::ORDER][$forType] = $ordered;
	}
	
	public function order($forType)
	{
	    //right key
        if(!isset($this->_data[self::ORDER][$forType]))
        {
            throw new XArgumentException(sprintf('key /order/%s not found', $forType));
        }
        return $this->_data[self::ORDER][$forType];
	}

    //options
	public function changeOption($forType, $andKey, $toValue)
	{
	    $this->_modified = true;
        if(!isset($this->_data[self::OPTIONS][$forType]) || !isset($this->_data[self::OPTIONS][$forType][$andKey]))
        {
            throw new XArgumentException(sprintf('key /options/%s/%s not found', $forType, $andKey));
        }
        $this->_data[self::OPTIONS][$forType][$andKey] = $toValue;
	}
    
	public function option($forType, $andKey)
	{
	    if(!isset($this->_data[self::OPTIONS][$forType]) || !isset($this->_data[self::OPTIONS][$forType][$andKey]))
        {
            throw new XArgumentException(sprintf('key /options/%s/%s not found', $forType, $andKey));
        }
        return $this->_data[self::OPTIONS][$forType][$andKey]; 
	}
	
	public function options($forType)
	{
	    if(!isset($this->_data[self::OPTIONS][$forType]))
        {
            throw new XArgumentException(sprintf('key /options/%s/%s not found', $forType, $andKey));
        }
        return $this->_data[self::OPTIONS][$forType]; 
	}
	//FIXME remove redundant redundancy
	
    
	/**
	 * @return CFeed
	 */
	public static function Create($title)
	{
	    $SCI = SContentIndex::alloc()->init();
	    list($dbid, $alias) = $SCI->createContent('CFeed', $title);
	    $tpl = new CFeed($alias);
	    new EContentCreatedEvent($tpl, $tpl);
	    return $tpl;
	}
	
	public static function Delete($alias)
	{
	    $SCI = SContentIndex::alloc()->init();
	    return $SCI->deleteContent($alias, 'CFeed');
	}
	
	public static function Exists($alias)
	{
	    $SCI = SContentIndex::alloc()->init();
	    return $SCI->exists($alias, 'CFeed');
	}
	
	/**
	 * [alias => [title, pubdate]]
	 * @return array
	 */
	public static function Index()
	{
	    $SCI = SContentIndex::alloc()->init();
	    return $SCI->getIndex('CFeed', false);;
	}
		
	public static function Open($alias)
	{
	    $SCI = SContentIndex::alloc()->init();
	    if($SCI->exists($alias, 'CFeed'))
	    {
	        return new CFeed($alias);
	    }
	    else
	    {
	        throw new XUndefinedIndexException($alias);
	    }
	}
	
	
	/**
	 * @param string $id
	 * @throws XFileNotFoundException
	 * @throws XFileLockedException
	 * @throws XInvalidDataException
	 */
	public function __construct($alias)
	{
	    if(!self::Exists($alias))
	    {
	        throw new XArgumentException('content not found');
	    }
	    $this->initBasicMetaFromDB($alias);
	    $dataFile = $this->StoragePath($this->Id);
	    if(file_exists($dataFile))
	    {
	        $this->_data = DFileSystem::LoadData($dataFile);
	    }
	}

	private function link($arg, $inTargetView = false)
	{
	    $iqo = $this->invokingQueryObject;
	    if(!$inTargetView && $iqo != null && $iqo instanceof QSpore)
	    {
	        //link to self
	        //only param page=
            $iqo->SetLinkParameter('page', $arg, true);
            $iqo->LinkTo($this->getAlias());
            return strval($iqo);
        }   
        elseif($inTargetView)
        {
            //link page to target view
            $linker = new QSpore($this->option(self::SETTINGS, 'TargetView'));
            $linker->LinkTo($arg);
            return strval($linker);
        } 
        else
        {
            return '#';        
        }
	}
	
	public function getFeedItemAliases()
	{
	    $aliases = array();
	    $res = QCFeed::getAliasesForFeed($this->Id);
	    while ($row = $res->fetch()) 
	    {
	    	$aliases[] = $row[0];
	    }
	    return $aliases;
	}
	
	/**
	 * Enter description here...
	 *
	 * @return string
	 */
	public function getContent()
	{
	    //fetch meta data
	    $res = QCFeed::countItemsForFeed($this->getId());
	    list($count) = $res->fetch();
        $res->free();
        
        $hasMorePages = true;
        
        //max items per page
        $itemsPerPage = $this->option(self::SETTINGS, 'ItemsPerPage');
        
        //available pages
        $pages = max(1,ceil($count/max(1,$itemsPerPage)));
        
        //current page        
        $currentPage = 1;
        $iqo = $this->invokingQueryObject;
        if($iqo != null && $iqo instanceof QSpore)
        {
            $currentPage = intval($iqo->GetParameter('page'));
        }
        $currentPage = max(1, $currentPage);
        //last page
        $maxPages = $this->option(self::SETTINGS, 'MaxPages') 
            ? min($this->option(self::SETTINGS, 'MaxPages'), $pages) 
            : $pages;
        
        //displayable items    
        $maxItems = min($maxPages * $itemsPerPage, $count);
        
        //page to display
        if($currentPage >= $maxPages)
        {
            $currentPage = min($currentPage, $maxPages);
            $hasMorePages = false;
        }
        
        //item count on page
        $startItem = ($currentPage-1)*$itemsPerPage+1;
        $endItem = min($startItem+$itemsPerPage-1, $maxItems);

        //which items to fetch?
        $fetch = array();
        foreach ($this->order(self::ITEM) as $prop => $rank) 
        {
        	if($rank != null)
        	{
        	    $fetch[] = $prop;
        	}
        }
        
        $res = QCFeed::getItemsForPage(
            $this->getId(), 
            $this->option(self::SETTINGS, 'SortBy'),
            $this->option(self::SETTINGS, 'SortOrder'),
            $currentPage,
            $itemsPerPage,
            $fetch
            );
        
        //html building
        $content = '<div id="CFeed_'.$this->getAlias().'" class="CFeed">';
        if($count > 0)
        {
            $content .= $this->buildControlHtml(self::HEADER, $hasMorePages, $currentPage, $startItem, $endItem, $maxItems);
            while($row = $res->fetch())
            {
                $content .= $this->buildItemHtml($row);
            }
            $res->free();
            $content .= $this->buildControlHtml(self::FOOTER, $hasMorePages, $currentPage, $startItem, $endItem, $maxItems);
        }
        else
        {
            $content .= '<p>'.implode('<br />', $this->caption(self::ITEM, 'NoItemsFound')).'</p>';
        }
        $content .= '</div>';
        return $content;
	}
	
	private function buildControlHtml($type, $hasMorePages, $Pagina, $NumberOfStart, $NumberOfEnd, $FoundItems)
	{
        $html = sprintf("\n\t<div class=\"CFeed_control CFeed_control_%s\">", ($type == self::HEADER) ? 'header' : 'footer');
        $tpl = "\n\t\t<%s class=\"CFeed_control_%s\">%s</%s>";
        
        foreach ($this->order($type) as $key => $pos) 
        {
            $set = false;
        	if(!$pos)
        	{
        	    continue;
        	}
        	$class = strtolower($key);
        	$tag = 'div';
        	switch ($key) 
        	{
        	    case 'NextLink' :
        	        if($hasMorePages)
        	        {
        	            $set = true;
                        $captions = $this->caption($type, 'Link');
                        $caption = $captions[self::SUFFIX];
                        $page = $Pagina + 1;
                        $content = sprintf('<a href="%s">%s</a>', $this->link($page), $caption);
        	        }
                    break;
    	        case 'PrevLink' :
        	        if($Pagina > 1)
        	        {
        	            $set = true;
                        $captions = $this->caption($type, 'Link');
                        $caption = $captions[self::PREFIX];
                        $page = $Pagina - 1;
                        $content = sprintf('<a href="%s">%s</a>', $this->link($page), $caption);
        	        }
                    break;
                case 'Pagina':
                case 'NumberOfStart' :
                case 'NumberOfEnd' :
                case 'FoundItems' :
                    $set = true;
                    $captions = $this->caption($type, $key);
        		    $content = $captions[self::PREFIX];
        		    $content .= ${$key};
        		    $content .= $captions[self::SUFFIX];
        		    break;
                default: continue;
        	}
        	if($set)
        	{
        	    $html .= sprintf($tpl, $tag, $class, $content, $tag);
        	}
        }
        $html .= "\n\t</div>";
        return $html;
	}
		
	private function buildItemHtml(array $data)
	{
        $map = array(
            'Title' => 0,
            'Description' => 1,
            'PubDate' => 2,
            'Alias' => 3,
            'Author' => 4,
            'ModDate' => 5,
            'Tags' => 6
        );
        $contentObject = null;
        
        $html = "\n\t<div class=\"CFeed_item\">\n\t\t<span class=\"CFeed_begin_item\"></span>";
        //add all active attributes in order
        $tpl = "\n\t\t<%s class=\"CFeed_item_%s\">%s</%s>";
        foreach ($this->order(self::ITEM) as $key => $pos) 
        {
        	if(!$pos)
        	{
        	    continue;
        	}
        	$class = strtolower($key);
        	$tag = 'span';
        	switch ($key) 
        	{
                case 'Tags':
        		    $tag = 'div';
        		    $content = htmlentities($data[$map[$key]], ENT_QUOTES, 'UTF-8');
        		    break;
                case 'Description':
        		    $tag = 'div';
        		    $content = $data[$map[$key]];
        		    break;
                case 'PreviewImage':
                    $co = $contentObject ? $contentObject : BContent::Open($data[$map['Alias']]);
        		    //do not cache content - it was not accessed here
                    $tag = 'div';
                    $content = $co->getPreviewImage()->scaled(
                        $this->option(self::ITEM, 'PreviewImageWidth'),
                        $this->option(self::ITEM, 'PreviewImageHeight'),
                        substr($this->option(self::ITEM, 'PreviewImageMode'),0,1),
                        substr($this->option(self::ITEM, 'PreviewImageMode'),1,1),
                        $this->option(self::ITEM, 'PreviewImageBgColor')
                    );
                    //100,100, WImage::MODE_FORCE,WImage::FORCE_BY_CROP, '#4e9a06');
                    break;
                case 'Icon':
                    $co = $contentObject ? $contentObject : BContent::Open($data[$map['Alias']]);
        		    //do not cache content - it was not accessed here
                    $tag = 'div';
                    $content = $co->getIcon()->asSize($this->option(self::ITEM, 'IconSize'));
                    break;
    		    case 'Content':
                    $co = $contentObject ? $contentObject : BContent::Access($data[$map['Alias']], $this);
                    $contentObject = $co;//cache accessed content
                    $tag = 'div';
                    $content = $co->getContent();
                    break;
                case 'Link':
        		    $content = sprintf('<a href="%s">%s</a>', $this->link($data[$map['Alias']], true), implode($this->caption(self::ITEM,'Link')));
        		    break;
                case 'Author':
                case 'PubDate':
                case 'ModDate':
        		    $content = htmlentities($data[$map[$key]], ENT_QUOTES, 'UTF-8');
        		    break;
                case 'Title':
        		    $tag = 'h2';
        		    $content = ($this->option(self::ITEM, 'LinkTitle')) 
        		        ? sprintf('<a href="%s">%s</a>', $this->link($data[$map['Alias']], true), htmlentities($data[$map[$key]], ENT_QUOTES, 'UTF-8'))
        		        : htmlentities($data[$map[$key]], ENT_QUOTES, 'UTF-8');
        		    break;
                default: continue;
        	}
        	$html .= sprintf($tpl, $tag, $class, $content, $tag);
        }
        $html .= "\n\t\t<span class=\"CFeed_end_item\"></span>\n</div>\n";
        return $html;
	}
	
	public function setContent($value)
	{
	    throw new XPermissionDeniedException('feeds are generated');
	}
	
    public function startFeedReading()
    {
        $this->FeedDBRes = QCFeed::getItemsForFeed($this->getId());
        $this->SCI = SComponentIndex::alloc()->init();
    }
	
    public function getFeedMetaData()
	{
	    if($this->FeedDBRes == null || !$this->FeedDBRes instanceof DSQLResult)
        {
            throw new XDatabaseException('feed query not initialized');
        }
        $spore = $this->option(self::SETTINGS, 'TargetView');
        $url = SLink::base().SLink::link(array($spore => $this->getAlias()), '', true);  
        return array(
            BFeed::TITLE => $this->getTitle(),
            BFeed::LINK => $url,
            BFeed::DESCRIPTION => $this->getDescription()
        );
	}
    
	public function hasMoreFeedItems()
	{
	    if($this->FeedDBRes == null || !$this->FeedDBRes instanceof DSQLResult)
        {
            throw new XDatabaseException('feed query not initialized');
        }
        return $this->FeedDBRes->hasNext();
	}
	
	public function getFeedItemData()
	{ 
	    if($this->FeedDBRes == null || !$this->FeedDBRes instanceof DSQLResult)
        {
            throw new XDatabaseException('feed query not initialized');
        }
//	        $xml->tag('title', array(), $row[0]);
//	        $xml->tag('desc', array(), $row[1]);
//	        $xml->tag('pubdate', array(), date('r', strtotime($row[2])));
//	        $xml->tag('link', array(), $row[3]);
//	        $xml->tag('lastmodified', array(), $row[4]);
//	        $xml->tag('categories', array(), $row[5]);
	    $row = $this->FeedDBRes->fetch();
	    $spore = $this->option(self::SETTINGS, 'TargetView');
	    $arr = array(
	        BFeed::TITLE => $row[0],
	        BFeed::DESCRIPTION  => $row[1],
	        BFeed::PUB_DATE  => $row[2],
	        BFeed::LINK  => SLink::base().SLink::link(array($spore => $row[3]), '', true)
	    );
	    if($this->SCI->IsImplementation($row[6], 'IFileContent'))
	    {
	        $arr[BFeed::ENCLOSURE] = array($row[0], array(
                BFeed::URL => sprintf(IFileContent::ENCLOSURE_URL, SLink::base(), $row[3]),
                BFeed::TYPE => $row[7],
                BFeed::LENGTH => $row[8]
            ));
	    }
	    return $arr;
	}
	
    public function finishFeedReading()
    {
        if($this->FeedDBRes != null && $this->FeedDBRes instanceof DSQLResult)
        {
            $this->FeedDBRes->free();
        }
    }
	
    public function Save()
	{
		//save content
		if($this->isModified())
		{
			DFileSystem::SaveData($this->StoragePath($this->Id),$this->_data);
    		QCFeed::setFeedType($this->Id,$this->option(CFeed::SETTINGS, 'FilterMethod'));
    		QCFeed::setFilterTags($this->Id, $this->option(CFeed::SETTINGS, 'Filter'));
    		$this->saveMetaToDB();
    		new EContentChangedEvent($this, $this);
    		if($this->_origPubDate != $this->PubDate)
    		{
    			$e = ($this->__get('PubDate') == 0)
    				? new EContentRevokedEvent($this, $this)
    				: new EContentPublishedEvent($this, $this);
    		}
		}
	}
	
	/**
	 * Icon for this filetype
	 * @return WIcon
	 */
	public static function defaultIcon()
	{
	    return new WIcon('CFeed', 'content', WIcon::LARGE, 'mimetype');
	}
	
	/**
	 * Icon for this object
	 * @return WIcon
	 */
	public function getIcon()
	{
	    return CFeed::defaultIcon();
	}
	
	//ISupportsSidebar
	public function wantsWidgetsOfCategory($category)
	{
		return in_array(strtolower($category), array('text', 'media', 'settings', 'information', 'search'));
	}
}
?>