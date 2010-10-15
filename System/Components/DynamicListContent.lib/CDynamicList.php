<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2009-07-21
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage Content
 */
class CDynamicList
    extends BContent
    implements
        ISupportsSidebar,
        IGlobalUniqueId,
        ISearchDirectives,
        Interface_Content_ScopeCallback,
        Interface_Content_HasScope
{
    const GUID = 'org.bambuscms.content.cdynamiclist';
    const CLASS_NAME = 'CDynamicList';

    public function getClassGUID()
    {
        return self::GUID;
    }

    protected function composites()
    {
        $cmp = parent::composites();
        $cmp[] = 'ContentFormatter';
        $cmp[] = 'ContentAggregator';
        return $cmp;
    }

	/**
	 * @return CDynamicList
	 */
	public static function Create($title)
	{
	    list($dbid, $alias) = QBContent::create(self::CLASS_NAME, $title);
	    QBContent::setMimeType($alias, 'text/calendar');
	    $content = new CDynamicList($alias);
	    $e = new EContentCreatedEvent($content, $content);
	    return $content;
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
	}

	/**
	 * Icon for this filetype
	 * @return View_UIElement_Icon
	 */
	public static function defaultIcon()
	{
	    return new View_UIElement_Icon(self::CLASS_NAME, 'calendar', View_UIElement_Icon::LARGE, 'mimetype');
	}

	/**
	 * Icon for this object
	 * @return View_UIElement_Icon
	 */
	public function getIcon()
	{
	    return CDynamicList::defaultIcon();
	}

	/**
	 * @return string
	 */
	public function getContent()
	{
	    try{
    	    $cal = Factory_Calendar::getSharedInstance()->createCalendar(
    	        Factory_Calendar::AS_XHTML,
    	        $this->getTitle()
            );
            if($cal instanceof View_Content_Calendar_XHTML_Calendar)
            {
                $cal->setContentFormatter($this->getChildContentFormatter());
            }
            return "<div id=\"_".$this->getGUID()."\">\n".$this->buildCalendar($cal)."\n</div>\n";
	    }catch (Exception $e){
	        echo $e;
	    }
	}

	public function setContent($value)
	{
	    throw new XPermissionDeniedException('can\'t set content');
	}

	protected function saveContentData() {/* FIXME CDynamicList saves on set and not here*/}

	/*FIXME interface Interface_Content_ScopeCallback*/
    public function getLinkWithScopeData(array $data)
    {
        $qu = clone $this->getParentView();
        foreach ($data as $k => $v)
        {
            $qu->SetLinkParameter($k, $v, true);
        }
        return strval($qu->LinkTo($this->getAlias()));       //return $this->getParentView()->SetLinkParameter()
    }

    public function getScopeData()
    {
    	//FIXME called in management without parent view
        $q = $this->getParentView();
        $page = 1;
        if($q instanceof VSpore)
        {
        	$page = $q->GetParameter('page');
        }
        if(!preg_match('/^\d+$/',$page))
        {
            $page = 1;
        }
        return array('page' => $page);
    }

    //interface Interface_Content_HasScope
    protected $aggregatorScope = null;

    public function getScope()
    {
        $agginst = $this->getContentAggregatorInstance();
        if($this->aggregatorScope === null && $agginst != false)
        {
            $this->aggregatorScope = new Aggregator_Scope_EventPage(
                $agginst,
                $this,
                5,
                1
            );
        }
        return $this->aggregatorScope;
    }

	//ISupportsSidebar
	public function wantsWidgetsOfCategory($category)
	{
		return in_array(strtolower($category), array('text', 'settings', 'information', 'search'));
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