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
class CCalendar
    extends BContent 
    implements 
        ISupportsSidebar, 
        IGlobalUniqueId,
        ISearchDirectives,
        IHeaderService,
        IFileContent,
        IFileCacheControl,
        IContentHeaders,
        Interface_Content_ScopeCallback,
        Interface_Content_HasScope
{
    const GUID = 'org.bambuscms.content.ccalendar';
    const CLASS_NAME = 'CCalendar';
    
    public function getClassGUID()
    {
        return self::GUID;
    }
    
    protected function composites()
    {
        $cmp = parent::composites();
        $cmp[] = 'ContentFormatter';
        return $cmp;
    }
    
    private $_contentLoaded = false;
    
    public function getFileCacheLifeTime()
    {
        return 10;// seconds
    }
    
	/**
	 * @return CCalendar
	 */
	public static function Create($title)
	{
	    list($dbid, $alias) = QBContent::create(self::CLASS_NAME, $title);
	    QBContent::setMimeType($alias, 'text/calendar');
	    $content = new CCalendar($alias);
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
	 * @return WIcon
	 */
	public static function defaultIcon()
	{
	    return new WIcon(self::CLASS_NAME, 'calendar', WIcon::LARGE, 'mimetype');
	}
	
	/**
	 * Icon for this object
	 * @return WIcon
	 */
	public function getIcon()
	{
	    return CCalendar::defaultIcon();
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
            return $this->buildCalendar($cal);
	    }catch (Exception $e){
	        echo $e;
	    }
	}
	
	public function setContent($value)
	{
	    throw new XPermissionDeniedException('can\'t set content');
	}
	
	public function Save()
	{
		parent::Save();
	}
	
	//IHeaderService
	public static function getHeaderServideItems($forAlias = null)
	{
	    return array('calendars' => Controller_Content::getSharedInstance()->contentGUIDIndex(self::CLASS_NAME));
	}
	
	public static function sendHeaderService($embedAlias, EWillSendHeadersEvent $e)
	{
	    $url = 'file.php?get='.$embedAlias;
	    $e->getHeader()->addLink(CHARSET,$url,null,'text/calendar',$embedAlias,'alternate');
	}
	
	//IFileContent
	public function getFileName()
	{
	    return $this->getTitle();
	}
	
    public function getType()
    {
        return 'ics';
    }
    
    public function getDownloadMetaData()
    {
        return array($this->getTitle().'.'.$this->getType(), $this->getMimeType(), null);
    }
/*FIXME interface Interface_Content_ScopeCallback
{*/
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
        $q = $this->getParentView();
        $page = $q->GetParameter('page');
        if(!preg_match('/^\d+$/',$page))
        {
            $page = 1;
        }
        return array('page' => $page);
    }
/*}*/
    
    /*interface Interface_Content_HasScope
{*/
    protected $aggregatorController = null;
    protected $aggregatorScope = null;
    protected $aggregator = null;
    
    protected function getAggregatorController()
    {
        if($this->aggregatorController === null)
        {
            $this->aggregatorController = new Controller_Aggregators();
        }
        return $this->aggregatorController;
    }
    
    protected function getAggregator()
    {
        if($this->aggregator === null)
        {
            $this->aggregator = $this->getAggregatorController()->getSavedAggregator('news');
        }
        return $this->aggregator;
    }
    
    public function getScope()
    {
        if($this->aggregatorScope === null)
        {
            $this->aggregatorScope = new Aggregator_Scope_EventPage(
                $this->getAggregator(),
                $this,
                5,
                1
            );
        }
        return $this->aggregatorScope;
    }
/*}*/
    
    protected function buildCalendar(Interface_Calendar_Calendar $cal, $asPage = true)
    {
        if($asPage)
        {
            //event-tag aggregator
            $ca = new Controller_Aggregators();
            $aggregator = $ca->getSavedAggregator('news');
            $scope = new Aggregator_Scope_EventPage($aggregator, $this, 5,1);
            //event horizon scope
            //get from aggregator
            $pageContents = $scope->getPageContents();
        }
        else
        {
            //get all FIXME use time horizon scope
            $pageContents = array();
            while($row = $res->fetch())
            {
                $pageContents[] = array($row[2],$row[0],$row[1]);
            }
            $res->free();
        }
        foreach ($pageContents as $item)
        {
            try
            {
                $cal->addEntry(
                    $cal->createEvent(
                        strtotime($item[1]),//start time
                        strtotime($item[2]),//end time
                        $item[0]//open by alias
                    )
                );
            }
            catch (Exception $e)
            {
                //ignore
            }
        }
        
        return strval($cal);
    }
    
    public function sendFileContent()
    {
	    $cal = Factory_Calendar::getSharedInstance()->createCalendar(
	        Factory_Calendar::AS_FILE, 
	        $this->getTitle()
        );
        echo $this->buildCalendar($cal);
    }
    
    public function getRawDataPath()
    {
        return null;
    }
    
    //IContentHeaders
    public function sendContentHeaders(IHeaderAPI $header)
    {
        $url = 'file.php?get='.$this->getAlias();
	    $header->addLink(CHARSET,$url,null,'text/calendar',$this->getTitle(),'alternate');
    }
    
	//ISupportsSidebar
	public function wantsWidgetsOfCategory($category)
	{
		return in_array(strtolower($category), array('text', 'settings', 'information', 'search'));
	}
	
	//ISearchDirectives
	public function allowSearchIndex()
	{
	    return BContent::isIndexingAllowed($this->getId());
	}
	public function excludeAttributesFromSearchIndex()
	{
	    return array();
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