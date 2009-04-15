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
        BContent::setMimeType($alias, RFiles::getType('CFile'));
	    //DFileSystem::SaveData('./Content/CFile/'.$dbid.'.meta.php',$metadata);
	    $file = new CFile($alias);
	    $file->Size = $metadata['size'];
	    $file->saveMetaToDB();
	    new EContentCreatedEvent($file, $file);
	    return $file;
	}
	
	public static function Delete($alias)
	{
	    $file = new CFile($alias);
	    $dbid = $file->getId();
	    unset($file);
	    try
	    {
    	    $succ = QBContent::deleteContent($alias);
    	    if($succ)
    	    {
    	        @unlink('./Content/CFile/'.$dbid.'.data');
    	    }
	    }
	    catch (XDatabaseException $d)
	    {
	        SNotificationCenter::report('warning', 'element_is_in_use_and_can_not_be_deleted');
	        $succ = false;
	    }
	    catch (Exception $e)
	    {
	        SNotificationCenter::report('warning', 'cfile_delete_failed');
	        $succ = false;
	    }
	    return $succ;
	}
	
	public static function Exists($alias)
	{
	    return parent::contentExists($alias, self::CLASS_NAME);
	}
	
	/**
	 * [alias => [title, pubdate, type, id]]
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
	        return new CFile($alias);
	    }
	    catch (XArgumentException $e)
	    {
	        throw new XUndefinedIndexException($alias);
	    }
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
	    if(!self::Exists($alias))
	    {
	        throw new XArgumentException('content not found');
	    }
	    $this->initBasicMetaFromDB($alias);
	    //$this->metadata = DFileSystem::LoadData('./Content/CFile/'.$this->getId().'.meta.php');
	}
	
	/**
	 * @return string
	 */
	public function getContent()
	{
	    if(in_array($this->getType(), array('jpg','jpeg','png','gif')))
	    {
	        $img = WImage::forContent($this);
	        $img = $img->scaled(640,480);
	    }
        return sprintf(
            '<div class="CFile" id="_'.htmlentities($this->getGUID(), ENT_QUOTES, 'UTF-8').'">'.
                (in_array($this->getType(), array('jpg','jpeg','png','gif'))
                    ? '<div class="CFile-preview">'.strval($img).'</div>'
                    : sprintf(
                    	'<img class="CFile-icon"src="%s" alt="%s" />',
                        WIcon::pathForMimeIcon($this->getMimeType(), WIcon::MEDIUM)
                        ,$this->getMimeType()
                    )
                ).
                '<div class="CFile-description">%s</div>'.
                '<div class="CFile-meta">'.
                    '<p class="CFile-meta-name">File name: %s</p>'.
                    '<p class="CFile-meta-size">Size: %s</p>'.
                '</div>'.
                '<div class="CFile-link">'.
                    '<p><a href="file.php?get=%s">%s</a></p>'.
                '</div>'.
            '</div>'
            
            ,$this->getDescription()
            ,$this->getFileName()
            ,DFileSystem::formatSize($this->getSize())
            ,$this->getAlias()
            ,SLocalization::get('download')
        );
	}
	
	public function enclosureURL()
	{
	    return sprintf('%sfile.php?get=%s',SLink::base(),$this->getAlias());
	    
	}
	
	public function setContent($value)
	{
	    throw new XPermissionDeniedException('files are read only');
	}
	
	public function Save()
	{
		$this->saveMetaToDB();
		new EContentChangedEvent($this, $this);
		if($this->_origPubDate != $this->PubDate)
		{
			$e = ($this->__get('PubDate') == 0)
				? new EContentRevokedEvent($this, $this)
				: new EContentPublishedEvent($this, $this);
		}
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
	
	
}
?>