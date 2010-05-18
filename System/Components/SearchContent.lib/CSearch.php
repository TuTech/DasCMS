<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2009-04-28
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage Content
 */
class CSearch
    extends BContent 
    implements 
        ISupportsSidebar, 
        IGlobalUniqueId,
        ISearchDirectives,
        IGeneratesFeed
{
    const GUID = 'org.bambuscms.content.csearch';
    const CLASS_NAME = 'CSearch';
    public function getClassGUID()
    {
        return self::GUID;
    }
    
    private $inFeedMode = false;
    
	/**
	 * @return CSearch
	 */
	public static function Create($title)
	{
	    list($dbid, $alias) = QBContent::create(self::CLASS_NAME, $title);
	    $tpl = new CSearch($alias);
	    new EContentCreatedEvent($tpl, $tpl);
	    return $tpl;
	}
	
	public static function Delete($alias)
	{
	    return parent::Delete($alias);
	}
		
	protected function composites()
	{
	    $composites = parent::composites();
	    $composites[] = 'TargetView';
	    return $composites;
	}
	
	/**
	 * @param string $id
	 * @throws XFileNotFoundException
	 * @throws XFileLockedException
	 * @throws XInvalidDataException
	 */
	public function __construct($alias)
	{
	    try
	    {
	        $this->initBasicMetaFromDB($alias, self::CLASS_NAME);
	    }
	    catch (XUndefinedIndexException $e)
	    {
	        throw new XArgumentException('content not found');
	    }
	    $this->loadConfig();
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
	    return CSearch::defaultIcon();
	}
	
	private function link($alias, $title)
	{
	    $link = '#';
	    try
	    {
	        $iqo = $this->getParentView();
    	    if($iqo != null && $iqo instanceof VSpore)
    	    {
    	        $link = $iqo->buildParameterName('_q');
    	        $open = $iqo->buildParameterName('_open');
    	        $value = $iqo->GetParameter('q', CHARSET);
    	        //link to self and tell us to open this page
    	        $link = SLink::link(array(
	                $link => $value,
	                $open => $alias
	            ));
            }
	    }
	    catch (Exception $e)
	    {
	        /* nothing to link to */
	    }
        return sprintf('<a href="%s">%s</a>', $link, $title);
	}
	
	private function highlight($text, $words)
	{
	    //$hl = preg_split('/\b/',strip_tags($value));
        foreach ($words as $embolden)
        {
            $embolden = preg_replace('/([\\\^\$\.\[\]\|\(\)\?\*\+\{\}])/', '\\\\1', $embolden);
            $text = preg_replace('/('.$embolden.')/mui', '<i>\\1</i>', $text);
        }
        return $text;
	}
	
	/**
	 * @return string
	 */
	public function getContent()
	{
	    $link = null;
	    $value = '';
	    $itemsPerPage = 15;
	    $hasMore = false;
	    try
	    {
	        $iqo = $this->getParentView();
    	    if($iqo != null && $iqo instanceof VSpore)
    	    {
    	        //have something to open?
        	    $open = $iqo->GetParameter('open', CHARSET);
        	    if(!empty($open) && Controller_Content::getSharedInstance()->contentExists($open))
        	    {
        	        //link this query to the doc to open
        	        header('Location: '.SLink::base().$iqo->LinkTo($open));
        	        return;
        	    }
                $link = $iqo->buildParameterName('_q');
                $pageLink = $iqo->buildParameterName('_page');
                $value = $iqo->GetParameter('q', CHARSET);
                $page = intval($iqo->GetParameter('page', CHARSET));
                $page = max(1, $page);
                
              //  $value = RURL::get($link, CHARSET);//. isset($_GET[$link]) ? $_GET[$link] : '';
            }
	    }
	    catch (Exception $e)
	    {
	        /* nothing to link to */
	    }
	    if($link == null)
	    {
	        $html  = '';
	    }
	    else
	    {
	        $str = '';
	        $f = SSearchIndexer::extractFeatures($value);
	        if(count($f))
	        {
	            $words = preg_split('/\s+/',strip_tags($value));
	            $tpl = "\n\t<div class=\"CSearch-result importance_%d\">\n\t\t%s<h2>%s</h2>%s%s\n\t</div>";
    	        $features = array();
    	        $res = QCSearch::getFeatureIds(array_keys($f));
    	        while ($row = $res->fetch())
    	        {
    	            $features[$row[0]] = $row[1];
    	        }
    	        $res->free();
    	        if(count($features))
    	        {
    	            $start = microtime(true);
    	            QCSearch::scoredContents($features);
        	        $res = QCSearch::getScoredContent($itemsPerPage, $page);
        	        $dur = microtime(true)-$start;
        	        //$str .= '<span class="duration">'.$dur.'</span>';
        	        $found = min($res->getRowCount(),$itemsPerPage);
        	        $hasMore = $res->getRowCount() > $itemsPerPage;
        	        for($i = 0; $i < $found; $i++)
        	        {
        	            $row = $res->fetch();
        	            $desc = strip_tags($row[2]);
        	            $desc = strlen($desc) > 100 ? substr($desc,0,97).'...' : $desc; 
        	            
        	            $str .= sprintf(
        	                $tpl, 
        	                round($row[4]*5),
        	                sprintf("\n\t\t<span class=\"importance\">%f</span>",$row[4]),
        	                $this->link($row[3],$this->highlight($row[0], $words)), 
        	                empty($row[1]) ? '' : "\n\t\t<h3>".$this->highlight($row[1], $words).'</h3>', 
        	                empty($desc) ? '' : "\n\t\t<p>".$this->highlight($desc, $words).'</p>'
        	                );
        	        }
        	        $res->free();
    	        }
	        }
    	    	
	        $prevlink = SLink::link(array(
                $link => $value,
                $pageLink => $page-1
            ));
	        $nextlink = SLink::link(array(
                $link => $value,
                $pageLink => $page+1
            ));
	        
            $html = sprintf(
            	"<div id=\"_%s\" class=\"CSearch\">\n\t%s\n\t%s\n\t%s\n</div>\n"
            	,$this->getGUID()
            	,$this->controls(self::ABOVE, ($page > 1 ? $prevlink : null), ($hasMore ? $nextlink : null), SLink::buildPath(), $link, $value, $page)
            	,$str	        
            	,$this->controls(self::BELOW, ($page > 1 ? $prevlink : null), ($hasMore ? $nextlink : null), SLink::buildPath(), $link, $value, $page)
        	);
	    }
	    return $html;
	}
	
	private function controls($mode, $prevlink, $nextlink, $formtarget, $formname, $formvalue, $pagenr)
	{
	    $control = '<div class="controls">';
	    //prev
	    if($prevlink != null && ($this->modes[self::PREV] & $mode))
	    {
	        $control .= $this->linkControl(self::PREV, $mode, $prevlink); 
	    }
	    //next
	    if($nextlink != null && ($this->modes[self::NEXT] & $mode))
	    {
	        $control .= $this->linkControl(self::NEXT, $mode, $nextlink); 
	    }
	    //form
	    if($this->modes[self::FORM] & $mode)
	    {
	        $control .= sprintf(
	        	"<form method=\"get\" action=\"index.php%s\">\n\t\t".
	                "<input type=\"text\" name=\"%s\" value=\"%s\"/>\n\t\t".
	                "<input type=\"submit\" value=\"%s\"/>\n\t".
	            "</form>\n"
	            ,$formtarget
	            ,$formname
	            ,htmlentities($formvalue, ENT_QUOTES, CHARSET)
	            ,htmlentities($this->captions[self::FORM][$mode], ENT_QUOTES, CHARSET)
            );
	    }
	    //overview
	    if($this->modes[self::OVERVIEW] & $mode)
	    {
	        $control .= '<span class="pageno">'.htmlentities($this->captions[self::OVERVIEW][$mode], ENT_QUOTES, CHARSET).$pagenr.'</span>';
	    }
	    return $control.'</div>';
	}
	
	private function linkControl($option, $mode, $url)
	{
	    return sprintf(
            ' <a class="%s" href="%s">%s</a> '
            ,$option == self::NEXT ? 'nextlink' : 'prevlink' 
        	,$url
        	,htmlentities($this->captions[$option][$mode], ENT_QUOTES, CHARSET)
    	);
	}
	
	public function setContent($value)
	{
	    throw new XFileLockedException('search has dynamic content');
	}
	
	protected function saveContentData()
	{
		//save content
		$this->dumbConfig();
	}
	
	//options
	const NEXT = 1;
	const PREV = 2;
	const OVERVIEW = 4;
	const FORM = 8;
	
	//modes
	const DISABLED = 0;
	const ABOVE = 1;
	const BELOW = 2;
	
	private $modes = array(
	    1 => 3,
	    2 => 3,
	    4 => 3,
	    8 => 3
	);
	
	private $captions = array(
	    1 => array(0,'>>','>>'),
	    2 => array(0,'<<','<<'),
	    4 => array(0,'Seite: ','Seite: '),
	    8 => array(0,'Suchen','Suchen')
	);
	
	private function dumbConfig()
	{
	    $data = array();
	    foreach ($this->modes as $option => $mode)
	    {
	        foreach(array(self::ABOVE, self::BELOW) as $cmode)
	        {
	            if($mode & $cmode)
    	        {
    	            $data[] = array($option, $cmode, $this->captions[$option][$cmode]);
    	        }
	        }
	    }
	    QCSearch::dumpConfig($this->getId(), $data);
	}
	
	private function loadConfig()
	{
	    $res = QCSearch::fetchConfig($this->getId());
	    while($row = $res->fetch())
	    {
	        list($opt, $mode, $capt) = $row;
	        $this->modes[$opt] = $this->modes[$opt] | $mode;
	        $this->captions[$opt][$mode] = $capt;
	    }
	    $res->free();
	}
	
	private function checkOption($option)
	{
	    if(!in_array($option, array(self::NEXT, self::PREV, self::OVERVIEW, self::FORM)))
	    {
	        throw new Exception('this is not an option');
	    }
	}
	
	/**
	 * set $mode (DISABLED, ABOVE, BELOW, ABOVE|BELOW) for $option (NEXT, PREV, OVERVIEW, FORM) 
	 * @param int $option
	 * @param int $mode
	 * @return void
	 */
	public function changeMode($option, $mode)
	{
	    $this->checkOption($option);
        $this->modes[$option] = $mode & self::ABOVE | $mode & self::BELOW;
	}
	
	public function getMode($option)
	{
	    $this->checkOption($option);
        return $this->modes[$option];
	}
	public function changeCaption($option, $mode, $caption)
	{
	    $this->checkOption($option);
	    if($mode & self::ABOVE)
	    {
	        $this->captions[$option][self::ABOVE] = $caption;
	    }
	    if($mode & self::BELOW)
	    {
	        $this->captions[$option][self::BELOW] = $caption;
	    }
	}
	
	public function getCaption($option, $mode)
	{
	    $this->checkOption($option);
	    if($mode & self::ABOVE)
	    {
	        return $this->captions[$option][self::ABOVE];
	    }
	    if($mode & self::BELOW)
	    {
	        return $this->captions[$option][self::BELOW];
	    }
	}
	
	//IGeneratesFeed
	/**
	 * list all aliases for feed use
	 * @return array
	 */
	public function getFeedItemAliases()
	{
	    $q = RURL::get('q', CHARSET);
	    $this->Title .= ': '.htmlentities($q, ENT_QUOTES, CHARSET);
	    $f = SSearchIndexer::extractFeatures($q);
        $features = array();
        $aliases = array();
        if(count($f))
        {
            $res = QCSearch::getFeatureIds(array_keys($f));
            while ($row = $res->fetch())
            {
                $features[$row[0]] = $row[1];
            }
            $res->free();
        }
        if(count($features))
        {
            QCSearch::scoredContents($features);
	        $res = QCSearch::getScoredContent(14, 1);
	        while ($row = $res->fetch())
	        {
	            $aliases[] = $row[3];
	        }
	        $res->free();
        }
        return $aliases;
	}
	
	public function getLinkToFeed()
	{
	    $value = '';
	    try
	    {
	        $iqo = $this->getParentView();
    	    if($iqo != null && $iqo instanceof VSpore)
    	    {
    	        //have something to open?
                $value = $iqo->GetParameter('q', CHARSET);
            }
	    }
	    catch (Exception $e)
	    {}
	    if(!empty($value))
	    {
	        return sprintf(
	        	'%s%s/%s?q=%s', 
	            SLink::base(), 
	            IGeneratesFeed::FEED_ACCESSOR, 
	            htmlentities($this->getAlias(), ENT_QUOTES, CHARSET), 
	            urlencode($value)
	        );
	    }
	    else 
	    {
	        return null;
	    }
	}
	
	public function getFeedTargetView()
	{
	    return $this->getTargetView();
	}
	
	//ISupportsSidebar
	public function wantsWidgetsOfCategory($category)
	{
		return in_array(strtolower($category), array('settings', 'information', 'search'));
	}
	//ISearchDirectives
	public function allowSearchIndex()
	{
	    return false;
	}
	public function excludeAttributesFromSearchIndex()
	{
	    return array();
	}
	public function isSearchIndexingEditable()
    {
        return false;
    }
    public function changeSearchIndexingStatus($allow)
    {
    }
}
?>