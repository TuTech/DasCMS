<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2008-10-17
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage Content
 */
class CFeed 
    extends BContent 
    implements 
        ISupportsSidebar, 
        IGlobalUniqueId, 
        IGeneratesFeed, 
        ISearchDirectives,
        IFileContent
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
    
    const PREFIX = 0;
    const SUFFIX = 1;
    
    const OPTIONS = 2;
    const ORDER = 1;
    const CAPTIONS = 0; 
    
    private $debug_mode = false;
    public function setDebug($on)
    {
        $this->debug_mode = $on == true;
    }
    
    private $lineNo = 0;
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
                'FoundItems' => array('',''),
                'Link' => array('',''),
                'Pagina' => array('','')
            ),
            self::ITEM => array(
                'Link' => array('',''),
                'NoItemsFound' => array('','')
            ),
            self::FOOTER => array(
                'NumberOfEnd' => array('',''),
                'NumberOfStart' => array('',''),
                'FoundItems' => array('',''),
                'Link' => array('',''),
                'Pagina' => array('','')
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
                'PreviewImage' => null,
                'SubTitle' => null
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
			    'LinkPreviewImage' => true,
			    'LinkIcon' => true,
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
            	'TargetFrame' => '',
                'SortOrder' => true,
                'SortBy' => 'title'
            )
        )
    );
    
    private function _getConfVal($type, $target, $key)
    {
        $this->assertOnFail(
            !isset($this->_data[$type]) || !isset($this->_data[$type][$target]) || !isset($this->_data[$type][$target][$key]), 
            $type, $target, $key);   
        return $this->_data[$type][$target][$key];
    }
    
    //captions
    public function changeCaption($forType, $andKey, $toPrefix, $andSuffix)
    {
        $this->assertOnFail(
            !isset($this->_data[self::CAPTIONS][$forType]) || !isset($this->_data[self::CAPTIONS][$forType][$andKey]), 
            'captions', $forType, $andKey);   
        $this->_modified = true;
        $this->_data[self::CAPTIONS][$forType][$andKey][self::PREFIX] = $toPrefix;
        $this->_data[self::CAPTIONS][$forType][$andKey][self::SUFFIX] = $andSuffix;
    }
    
    public function caption($forType, $andKey, $item = null)
    {
        $this->assertOnFail(
            !isset($this->_data[self::CAPTIONS][$forType]) || !isset($this->_data[self::CAPTIONS][$forType][$andKey]), 
            'captions', $forType, $andKey);    
        if($item === null && ($item == 0 || $item == 1))
        {
            return $this->_data[self::CAPTIONS][$forType][$andKey];
        }
        else
        {
             return $this->_data[self::CAPTIONS][$forType][$andKey][$item];
        }
    }
    
    //order of elements in the given section (1..n - unused are null)
	public function changeOrder($forType, array $toData)
	{
        $this->assertOnFail(
            !isset($this->_data[self::ORDER][$forType]), 
            'order', $forType, '');    
	    $this->_modified = true;
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
        $this->assertOnFail(
            !isset($this->_data[self::ORDER][$forType]), 
            'order', $forType, '');
        return $this->_data[self::ORDER][$forType];
	}

    //options
	public function changeOption($forType, $andKey, $toValue)
	{
	    $this->assertOnFail(
	        !isset($this->_data[self::OPTIONS][$forType]) || !isset($this->_data[self::OPTIONS][$forType][$andKey]), 
	        'options', $forType, $andKey);
	    $this->_modified = true;
        $this->_data[self::OPTIONS][$forType][$andKey] = $toValue;
        if($forType == self::SETTINGS && $andKey == 'TargetView')
        {
            $this->bindSelfToView($toValue);
        }
	}
    
	public function option($forType, $andKey)
	{
        $this->assertOnFail(
	        !isset($this->_data[self::OPTIONS][$forType]) || !isset($this->_data[self::OPTIONS][$forType][$andKey]), 
	        'options', $forType, $andKey);
        return isset($this->_data[self::OPTIONS][$forType][$andKey]) 
            ? $this->_data[self::OPTIONS][$forType][$andKey]
            : ''; 
	}
	
	public function options($forType)
	{
	    $this->assertOnFail(
	        !isset($this->_data[self::OPTIONS][$forType]), 
	        'options', $forType, '');
        return $this->_data[self::OPTIONS][$forType]; 
	}
	
	private function assertOnFail($failed, $section, $type, $key)
	{
	    if($failed && $this->debug_mode)
	    {
	        throw new XArgumentException(sprintf('key /%s/%s/%s not found', $section, $type, $key));
	    }
	}
    
	/**
	 * @return CFeed
	 */
	public static function Create($title)
	{
	    list($dbid, $alias) = QBContent::create(self::CLASS_NAME, $title);
	    $tpl = new CFeed($alias);
	    new EContentCreatedEvent($tpl, $tpl);
	    return $tpl;
	}
	
	public static function Delete($alias)
	{
	    return parent::Delete($alias);
	}
	
	public static function Exists($alias)
	{
	    return parent::contentExists($alias, self::CLASS_NAME);
	}
	
	/**
	 * [alias => [title, pubdate]]
	 * @return array
	 */
	public static function Index()
	{
	    return parent::getIndex(self::CLASS_NAME, false);
	}
		
	public static function Open($alias)
	{
	    try
	    {
	        return new CFeed($alias);
	    }
	    catch (XArgumentException $e)
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

	private function link($arg, $content, $inTargetView = false, $inTargetFrame = true)
	{
	    $link = null;
	    try
	    {
	        $iqo = $this->invokingQueryObject;
	        $target = $this->option(self::SETTINGS, 'TargetView');
    	    if($inTargetView && !empty($target) && VSpore::isActive($target))
            {
                //link page to target view
                $linker = new VSpore($this->option(self::SETTINGS, 'TargetView'));
                $linker->LinkTo($arg);
                $link = strval($linker);
            }
            elseif(!$inTargetView && $iqo != null && $iqo instanceof VSpore)
    	    {
    	        //link to self
    	        //only param page=
                $iqo->SetLinkParameter('page', $arg, true);
                $iqo->LinkTo($this->getAlias());
                $link = strval($iqo);
            }
	    }
	    catch (Exception $e)
	    {
	        /* nothing to link to */
	    }
	    if($link != null)
	    {
	        $targetFrame = $this->option(self::SETTINGS, 'TargetFrame');
	        $targetFrame = (empty($targetFrame) || !$inTargetFrame) ? '' : ' target="'.htmlentities($targetFrame, ENT_QUOTES, 'UTF-8').'"';
	        $content = sprintf('<a href="%s"%s>%s</a>', $link, $targetFrame, $content);
	    }
	    return $content;
	}
	
	/**
	 * list all aliases for feed use
	 * @return array
	 */
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
	
	public function getLinkToFeed()
	{
	    return sprintf(
	    	'%s%s/%s', 
	        SLink::base(), 
	        IGeneratesFeed::FEED_ACCESSOR, 
	        htmlentities($this->getAlias(), ENT_QUOTES, 'utf-8')
        );
	}
	
	public function getFeedTargetView()
	{
	    return $this->option(CFeed::SETTINGS, 'TargetView');;
	}
	
	/**
	 * @return string
	 */
	public function getContent()
	{
	    $this->lineNo = 0;
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
        if($iqo != null && $iqo instanceof VSpore)
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
        $content = '<div id="_'.$this->getGUID().'" class="CFeed">';
        if($count > 0)
        {
            $content .= $this->buildControlHtml(self::HEADER, $hasMorePages, $currentPage, $startItem, $endItem, $maxItems);
            $content .= "\n\t<div class=\"CFeed_items\">";
            while($row = $res->fetch())
            {
                $content .= $this->buildItemHtml($row);
            }
            $res->free();
            $content .= "\n\t</div>";
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
                        $content = $this->link($page, $caption,false,false);
        	        }
                    break;
    	        case 'PrevLink' :
        	        if($Pagina > 1)
        	        {
        	            $set = true;
                        $captions = $this->caption($type, 'Link');
                        $caption = $captions[self::PREFIX];
                        $page = $Pagina - 1;
                        $content = $this->link($page, $caption,false,false);
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
	    //db result order
        $map = array(
            'Title' => 0,
            'Description' => 1,
            'PubDate' => 2,
            'Alias' => 3,
            'Author' => 4,
            'ModDate' => 5,
            'Tags' => 6,
            'SubTitle' => 7
        );
        $contentObject = null;
        
        $html = sprintf("\n\t\t<div class=\"CFeed_item CFeed_item_no_%d\">\n\t\t\t<span class=\"CFeed_begin_item\"></span>", ++$this->lineNo);
        //add all active attributes in order
        $tpl = "\n\t\t\t<%s class=\"CFeed_item_%s\">%s</%s>";
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
                    )->asPreviewImage();
                    try
                    {
                    $content = ($this->option(self::ITEM, 'LinkPreviewImage')) 
        		        ? $this->link($data[$map['Alias']],$content,true)
        		        : $content;
                    }catch (Exception $e){/*legacy FIXME remove try-catch before release*/}
                    //100,100, WImage::MODE_FORCE,WImage::FORCE_BY_CROP, '#4e9a06');
                    break;
                case 'Icon':
                    $co = $contentObject ? $contentObject : BContent::Open($data[$map['Alias']]);
        		    //do not cache content - it was not accessed here
                    $tag = 'div';
                    $content = $co->getIcon()->asSize($this->option(self::ITEM, 'IconSize'));
                    $content = ($this->option(self::ITEM, 'LinkIcon')) 
        		        ? $this->link($data[$map['Alias']],$content,true)
        		        : $content;
                    break;
    		    case 'Content':
                    $co = $contentObject ? $contentObject : BContent::Access($data[$map['Alias']], $this);
                    $contentObject = $co;//cache accessed content
                    $tag = 'div';
                    $content = $co->getContent();
                    break;
                case 'Link':
        		    $content = $this->link($data[$map['Alias']], implode($this->caption(self::ITEM,'Link')), true);
        		    break;
                case 'Author':
                    $content = htmlentities($data[$map[$key]], ENT_QUOTES, 'UTF-8');
        		    break;
                case 'PubDate':
                case 'ModDate':
                    $datetime = $data[$map[$key]];
                    if($this->option(self::ITEM, $key.'Format') != '')
                    {
                        SErrorAndExceptionHandler::muteErrors();
                        $time = strtotime($datetime);
                        $datetime = date($this->option(self::ITEM, $key.'Format'), $time);
                        SErrorAndExceptionHandler::reportErrors();
                    }
        		    $content = htmlentities($datetime, ENT_QUOTES, 'UTF-8');
        		    break;
                case 'Title':
        		    $tag = 'h2';
        		    $content = ($this->option(self::ITEM, 'LinkTitle')) 
        		        ? $this->link($data[$map['Alias']],htmlentities($data[$map[$key]], ENT_QUOTES, 'UTF-8'),true)
        		        : htmlentities($data[$map[$key]], ENT_QUOTES, 'UTF-8');
        		    break;
                case 'SubTitle':
        		    $tag = 'h3';
        		    $content = $data[$map[$key]];
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
	    return new WIcon(self::CLASS_NAME, 'content', WIcon::LARGE, 'mimetype');
	}
	
	/**
	 * Icon for this object
	 * @return WIcon
	 */
	public function getIcon()
	{
	    return CFeed::defaultIcon();
	}
	
	//IFileContent
	public function getFileName()
	{
	    return $this->getTitle();
	}
	
    public function getType()
    {
        return 'xml';
    }
    
    public function getDownloadMetaData()
    {
        return array($this->getTitle().'.xml', 'application/xml', null);
    }
    
    public function sendFileContent()
    {
        echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        echo "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
        $spore = $this->option(self::SETTINGS, 'TargetView');
        if(!empty($spore))
        {
            $base = SLink::base();  
            $res = QCFeed::getSiteMapData($this->getId());
            while ($row = $res->fetch())
            {
                echo "\t<url>\n";
                echo "\t\t<loc>";
                echo $base, SLink::link(array($spore => $row[0]), '', true);
                echo "</loc>\n";
                echo "\t\t<lastmod>";
                echo date('c', strtotime($row[1]));
                echo "</lastmod>\n";
                echo "\t</url>\n";
            }
        }
        echo "</urlset>";
    }
    
    public function getRawDataPath()
    {
        return null;
    }
    
	//ISupportsSidebar
	public function wantsWidgetsOfCategory($category)
	{
		return in_array(strtolower($category), array('settings', 'information', 'search'));
	}
	//ISearchDirectives
	public function allowSearchIndex()
	{
	    return BContent::isIndexingAllowed($this->getId());
	}
	public function excludeAttributesFromSearchIndex()
	{
	    return array('Content');
	}
	public function isSearchIndexingEditable()
    {
        return true;
    }
    public function changeSearchIndexingStatus($allow)
    {
        QBContent::setAllowSearchIndexing($this->getId(), !empty($allow));
    }
}
?>