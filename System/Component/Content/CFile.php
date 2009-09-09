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
	    list($dbid, $alias) = QBContent::create('CFile', $title);
	    if(!RFiles::move('CFile', './Content/CFile/'.$dbid.'.data'))
	    {
	        throw new XUndefinedException('upload not moveable');
	    }
	    QCFile::saveFileMeta(
	        $dbid,
	        RFiles::getName('CFile'), 
	        DFileSystem::suffix(RFiles::getName('CFile')),
	        md5_file('./Content/CFile/'.$dbid.'.data',false)
        );
	    $metadata = array(
	        'filename' => RFiles::getName('CFile'), 
	        'type' => RFiles::getType('CFile'),
	        'md5' => md5_file('./Content/CFile/'.$dbid.'.data',false),
	        'size' => RFiles::getSize('CFile'),
	        'suffix' => DFileSystem::suffix(RFiles::getName('CFile'))
        );
        $type = RFiles::getType('CFile');
        if(DFileSystem::suffix(RFiles::getName('CFile')) == 'pdf')
        {
            $type = 'application/pdf';
        }
        QBContent::setMimeType($alias, $type);
	    $file = new CFile($alias);
	    $file->Size = $metadata['size'];
	    $file->saveMetaToDB();
	    new EContentCreatedEvent($file, $file);
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
	    QCFile::saveFileMeta(
	        $this->getId(),
	        RFiles::getName('CFile'), 
	        DFileSystem::suffix(RFiles::getName('CFile')),
	        md5_file('./Content/CFile/'.$this->getId().'.data',false)
        );
		$this->ModifiedBy = PAuthentication::getUserID();
		$this->ModifyDate = time();
        $this->Size = RFiles::getSize('CFile');
        QBContent::setMimeType($this->getAlias(), RFiles::getType('CFile'));
        $this->saveMetaToDB();
        SNotificationCenter::report('message', 'file_updated');
        $fs = DFileSystem::FilesOf('Content/temp/','/^scale\.render\.[\d]+\.'.$this->getId().'-/');
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
        $e = new EContentChangedEvent($this, $this);
	}
	
	protected function loadFileMetaData()
	{
	    if(count($this->metadata) == 0)
	    {
	        $metadata = array(
	            'folder' => '',
	            'folderID' => '',
	            'filename' => '', 
    	        'type' => '',
    	        'md5' => '',
    	        'size' => '',
    	        'suffix' => ''
	        );
	        $res = QCFile::getMetaData($this->Id);
	        list(
	            $metadata['folder'],
	            $metadata['filename'],
	            $metadata['suffix'],
	            $metadata['md5'],
	            $metadata['folderID']
            ) = $res->fetch();
            $this->metadata = $metadata;
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
	        $rendering = LConfiguration::getOrDefault('CFile_image_rendering_method', '0c');
	        $img = WImage::forContent($this);
	        $img = $img->scaled(
	            LConfiguration::getOrDefault('CFile_image_width', 640),
	            LConfiguration::getOrDefault('CFile_image_height', 480),
	            substr($rendering,0,1),
	            substr($rendering,1,1),
	            LConfiguration::getOrDefault('CFile_image_background_color', '#ffffff')
	        );
	    }
        return sprintf(
            '<div class="CFile" id="_'.htmlentities($this->getGUID(), ENT_QUOTES, CHARSET).'">'.
                (in_array($this->getType(), array('jpg','jpeg','png','gif'))
                    ? '<div class="CFile-preview">'.strval($img).'</div>'
                    : '<div class="CFile-icon">'.$this->getIcon()->asSize(WIcon::LARGE).'</div>'
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
            ,LConfiguration::get('CFile_download_target_blank') == '1' ? 'target="_blank" ' : ''
            ,LConfiguration::get('wellformed_urls') == '' ? '?get=' : '/'
            ,$this->getAlias()
            ,htmlentities(LConfiguration::get('CFile_download_text') == '' ? $this->getFileName() : LConfiguration::get('CFile_download_text'),ENT_QUOTES, CHARSET)
        );
	}
	
	public function setContent($value)
	{
	    throw new XPermissionDeniedException('files are read only');
	}
	
	public function Save()
	{
		parent::Save();
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
	 * @return WIcon
	 */
	public static function defaultIcon()
	{
	    return new WIcon('BContent', 'content', WIcon::LARGE, 'mimetype');
	}
	
	/**
	 * Icon for this object
	 * @return WIcon
	 */
	public function getIcon()
	{
	    return new WIcon($this->getType(), 'content', WIcon::LARGE, 'mimetype');
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
	
	//file management
	public static function getFolders()
	{
	    $folders = array('0' => SLocalization::get('unassigned_files'));
	    $res = QCFile::getChildFolders();
	    while($row = $res->fetch())
	    {
	        $folders[$row[0]] = $row[1];
	    }
	    $res->free();
	    return $folders;
	}
	
	public static function getFilesOfFolder($fid)
	{
	    $contents = array();
	    //Contents.contentID, Aliases.alias, Contents.title, Contents.size Mimetypes.mimetype
	    $res = QCFile::getFolderContents(($fid == 0 ? null : $fid));
	    while($row = $res->fetch())
	    {
	        $contents[$row[0]] = array($row[1], $row[2], $row[3], $row[4]);
	    }
	    $res->free();
	    return $contents;
	}
	
	public static function createFolder($name)
	{
	    QCFile::createFolder($name);
	}
	
	public static function deleteFolder($fid)
	{
	    QCFile::deleteFolder($fid);
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