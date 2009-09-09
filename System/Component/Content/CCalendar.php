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
        IContentHeaders
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
	    $cal = Factory_Calendar::getSharedInstance()->createCalendar(
	        Factory_Calendar::AS_XHTML, 
	        $this->getTitle()
        );
        if($cal instanceof View_Content_Calendar_XHTML_Calendar)
        {
            $cal->setContentFormatter($this->getChildContentFormatter());
        }
        return $this->buildCalendar($cal);
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
    
    protected function buildCalendar(Interface_Calendar_Calendar $cal)
    {
        $res = QCCalendar::getEvents();
        while($row = $res->fetch())
        {
            try
            {
                $cal->addEntry(
                    $cal->createEvent(
                        strtotime($row[0]),//start time
                        strtotime($row[1]),//end time
                        $row[2]//open by alias
                    )
                );
            }
            catch (Exception $e)
            {
                //ignore
            }
        }
        $res->free();
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