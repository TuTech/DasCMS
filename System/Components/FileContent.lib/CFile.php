<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2008-11-03
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage Content
 */
class CFile
    extends BContent 
    implements 
        ISupportsSidebar, 
        IGlobalUniqueId,
        IFileContent,
        ISearchDirectives,
        Interface_XML_Atom_ProvidesOutOfLineContent
{
    const GUID = 'org.bambuscms.content.cfile';
    const CLASS_NAME = 'CFile';
    
    public function getClassGUID()
    {
        return self::GUID;
    }
    
    protected $RAWContent;
    private $_contentLoaded = false;
    private $metadata = array();
	/**
	 * @return CFile
	 */
	public static function Create($title)
	{
	    //FIXME RFiles::tempName(CFile)
	    $title = empty($title) ? RFiles::getName('CFile') : $title;
	    if(!RFiles::hasFile('CFile'))
	    {
	        throw new XFileNotFoundException('no uploaded file', 'CFile');
	    }
	    list($dbid, $alias) = BContent::createContent('CFile', $title);
	    if(!RFiles::move('CFile', './Content/CFile/'.$dbid.'.data'))
	    {
	        throw new XUndefinedException('upload not moveable');
	    }
	    self::saveFileMeta(
	        $dbid,
	        RFiles::getName('CFile'), 
	        DFileSystem::suffix(RFiles::getName('CFile')),
	        md5_file('./Content/CFile/'.$dbid.'.data',false)
        );
        $type = RFiles::getType('CFile');
        if(DFileSystem::suffix(RFiles::getName('CFile')) == 'pdf')
        {
            $type = 'application/pdf';
        }
		BContent::setMIMEType($alias, $type);
	    $file = new CFile($alias);
	    $file->Size = RFiles::getSize('CFile');
	    $file->saveMetaToDB();
	    new Event_ContentCreated($file, $file);
	    return $file;
	}
	
	/**
	 * update file content
	 * @return void
	 */
	public function updateData()
	{
    	if(!RFiles::hasFile('CFile'))
	    {    
	        throw new XFileNotFoundException('no uploaded file', 'CFile');
	    }
	    if(!RFiles::move('CFile', './Content/CFile/'.$this->getId().'.data'))
	    {
	        throw new XUndefinedException('upload not moveable');
	    }
		
		$e = new Event_WillSaveContent($this, $this);
		if($e->isCanceled()){
			return;//notifications are up to the canceling object
		}
		self::saveFileMeta(
	        $this->getId(),
	        RFiles::getName('CFile'), 
	        DFileSystem::suffix(RFiles::getName('CFile')),
	        md5_file('./Content/CFile/'.$this->getId().'.data',false)
        );
		$this->ModifiedBy = PAuthentication::getUserID();
		$this->ModifyDate = time();
        $this->Size = RFiles::getSize('CFile');
        BContent::setMIMEType($this->getAlias(), RFiles::getType('CFile'));
        $this->saveMetaToDB();
        SNotificationCenter::report('message', 'file_updated');
        $fs = DFileSystem::filesOf('Content/temp/','/^scale\.render\.[\d]+\.'.$this->getId().'-/');
        SErrorAndExceptionHandler::muteErrors();
        foreach ($fs as $file)
        {
            if(@unlink('Content/temp/'.$file))
            {
                SNotificationCenter::report('message', 'cached_rendering_deleted');
            }
            else
            {
                SNotificationCenter::report('warning', 'could_not_delete_cached_rendering');
            }
        }
        SErrorAndExceptionHandler::reportErrors();
        $e = new Event_ContentChanged($this, $this);
	}

	protected static function saveFileMeta($id, $fileName, $suffix, $md5)
	{
		return Core::Database()
			->createQueryForClass('CFile')
			->call('setFileMeta')
			->withParameters($id, $fileName, $suffix, $md5, $fileName, $suffix, $md5)
			->execute();
	}




	protected function loadFileMetaData()
	{
	    if(count($this->metadata) == 0)
	    {
	        $this->metadata = array(
	            'folder' => '',
	            'folderID' => '',
	            'filename' => '', 
    	        'type' => '',
    	        'md5' => '',
    	        'size' => '',
    	        'suffix' => ''
	        );
	        $res = Core::Database()
				->createQueryForClass('CFile')
				->call('getMetaData')
				->withParameters($this->Id);
	        list(
	            $this->metadata['filename'],
	            $this->metadata['suffix'],
	            $this->metadata['md5']
            ) = $res->fetchResult();
			$res->free();
	    }
	}
	
	/**
	 * @param string $alias
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
	 * @return string
	 */
	public function getContent()
	{
	    if(in_array($this->getType(), array('jpg','jpeg','png','gif')))
	    {
	        $rendering = Core::settings()->getOrDefault('CFile_image_rendering_method', '0c');
	        $img = View_UIElement_Image::forContent($this);
	        $img = $img->scaled(
	            Core::settings()->getOrDefault('CFile_image_width', 640),
	            Core::settings()->getOrDefault('CFile_image_height', 480),
	            substr($rendering,0,1),
	            substr($rendering,1,1),
	            Core::settings()->getOrDefault('CFile_image_background_color', '#ffffff')
	        );
	    }
        return sprintf(
            '<div class="CFile" id="_'.htmlentities($this->getGUID(), ENT_QUOTES, CHARSET).'">'.
                (in_array($this->getType(), array('jpg','jpeg','png','gif'))
                    ? '<div class="CFile-preview">'.strval($img).'</div>'
                    : '<div class="CFile-icon">'.$this->getIcon()->asSize(View_UIElement_Icon::LARGE).'</div>'
                ).
                '<div class="CFile-description">%s</div>'.
                '<div class="CFile-meta">'.
                    '<p class="CFile-meta-size">%s: %s</p>'.
                '</div>'.
                '<div class="CFile-link">'.
                    '<p><a %shref="file.php%s%s">%s</a></p>'.
                '</div>'.
            '</div>'
            
            ,$this->getDescription()
            ,SLocalization::get('file_size')
            ,DFileSystem::formatSize($this->getSize())
            ,Core::settings()->get('CFile_download_target_blank') == '1' ? 'target="_blank" ' : ''
            ,Core::settings()->get('wellformed_urls') == '' ? '?get=' : '/'
            ,$this->getAlias()
            ,htmlentities(Core::settings()->get('CFile_download_text') == '' ? $this->getFileName() : Core::settings()->get('CFile_download_text'),ENT_QUOTES, CHARSET)
        );
	}
	
	public function setContent($value)
	{
	    throw new XPermissionDeniedException('files are read only');
	}
	
	protected function saveContentData()
	{
	}
	
    public function getMD5Sum()
    {
        $this->loadFileMetaData();
        return $this->metadata['md5'];
    }
    
    //Interface_XML_Atom_ProvidesOutOfLineContent
    public function getOutOfLineType()
    {
        return $this->getMimeType();
    }
    
    public function getOutOfLineURI()
    {
        return sprintf(IFileContent::ENCLOSURE_URL, SLink::base(), $this->getAlias());
    }
    
    	/**
	 * Icon for this filetype
	 * @return View_UIElement_Icon
	 */
	public static function defaultIcon()
	{
	    return new View_UIElement_Icon('BContent', 'content', View_UIElement_Icon::LARGE, 'mimetype');
	}
	
	/**
	 * Icon for this object
	 * @return View_UIElement_Icon
	 */
	public function getIcon()
	{
	    return new View_UIElement_Icon($this->getType(), 'content', View_UIElement_Icon::LARGE, 'mimetype');
	}
	
    
	//IFileContent
	public function getFileName()
	{
	    $this->loadFileMetaData();
	    return $this->metadata['filename'];
	}
	
    public function getType()
    {
        $this->loadFileMetaData();
        return $this->metadata['suffix'];
    }
    
    public function getDownloadMetaData()
    {
        $this->loadFileMetaData();
        return array($this->metadata['filename'], $this->getMimeType(), $this->getSize());
    }
    
    public function sendFileContent()
    {
        readfile('./Content/CFile/'.$this->getId().'.data');
    }
    
    public function getRawDataPath()
    {
        return './Content/CFile/'.$this->getId().'.data';
    }
    
	//ISupportsSidebar
	public function wantsWidgetsOfCategory($category)
	{
		return in_array(strtolower($category), array('binary', 'data', 'settings', 'information', 'search'));
	}
	
	public static function getFilesOfFolder($fid)
	{
	    $contents = array();
	    //Contents.contentID, Aliases.alias, Contents.title, Contents.size Mimetypes.mimetype
		$res = Core::Database()
			->createQueryForClass('CFile')
			->call('getContents')
			->withoutParameters();
	    while($row = $res->fetchResult())
	    {
	        $contents[$row[0]] = array($row[1], $row[2], $row[3], $row[4]);
	    }
	    $res->free();
	    return $contents;
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
        BContent::setIndexingAllowed($this->getId(), !empty($allow));
    }
	
}
?>