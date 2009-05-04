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
        ISearchDirectives
{
    const GUID = 'org.bambuscms.content.csearch';
    const CLASS_NAME = 'CSearch';
    public function getClassGUID()
    {
        return self::GUID;
    }
    
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
	        return new CSearch($alias);
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
	        $iqo = $this->invokingQueryObject;
    	    if($iqo != null && $iqo instanceof VSpore)
    	    {
    	        $link = $iqo->buildParameterName('_q');
    	        $open = $iqo->buildParameterName('_open');
    	        $value = $iqo->GetParameter('q', 'UTF-8');
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
	        $iqo = $this->invokingQueryObject;
    	    if($iqo != null && $iqo instanceof VSpore)
    	    {
    	        //have something to open?
        	    $open = $iqo->GetParameter('open', 'UTF-8');
        	    if(!empty($open) && BContent::contentExists($open))
        	    {
        	        //link this query to the doc to open
        	        header('Location: '.SLink::base().$iqo->LinkTo($open));
        	        return;
        	    }
                $link = $iqo->buildParameterName('_q');
                $pageLink = $iqo->buildParameterName('_page');
                $value = $iqo->GetParameter('q', 'UTF-8');
                $page = intval($iqo->GetParameter('page', 'UTF-8'));
                $page = max(1, $page);
                
              //  $value = RURL::get($link, 'UTF-8');//. isset($_GET[$link]) ? $_GET[$link] : '';
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
    	            $str .= '<span class="pageno">'.$page.'</span>';
    	            $start = microtime(true);
    	            QCSearch::scoredContents($features);
        	        //$res = QCSearch::getWeightedContent($features, $itemsPerPage, $page);
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
	        $html = 
    	        "<div id=\"_%s\" class=\"CSearch\">\n\t".	
    	    		"<form method=\"get\" action=\"%s%s\">\n\t\t".
    	                "<input type=\"text\" name=\"%s\" value=\"%s\"/>\n\t\t".
    	                "<input type=\"submit\" value=\"%s\"/>\n\t".
    	            "</form>%s\n";
    	    	
	        $html = sprintf($html, $this->getGUID(), 'index.php', SLink::buildPath(), $link, htmlentities($value, ENT_QUOTES, 'UTF-8'),SLocalization::get('search'),$str);
	        if($page > 1)
	        {
	            //back link
	            $html .= sprintf(' <a class="prevlink" href="%s">&lt;&lt;</a> ',SLink::link(array(
	                $link => $value,
	                $pageLink => $page-1
	            )));
	        }
	        if($hasMore)
	        {
	            //next link
	            $html .= sprintf(' <a class="nextlink" href="%s">&gt;&gt;</a> ',SLink::link(array(
	                $link => $value,
	                $pageLink => $page+1
	            )));
	        }
	        $html .= "</div>\n";
	    }
	    return $html;
	}
	
	public function setContent($value)
	{
	    throw new XFileLockedException('search has dynamic content');
	}
	
	public function Save()
	{
		//save content
		$this->saveMetaToDB();
		new EContentChangedEvent($this, $this);
		if($this->_origPubDate != $this->PubDate)
		{
			$e = ($this->__get('PubDate') == 0)
				? new EContentRevokedEvent($this, $this)
				: new EContentPublishedEvent($this, $this);
		}
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
}
?>