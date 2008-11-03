<?php
/**
 * @package Bambus
 * @subpackage Contents
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2008-11-03
 * @license GNU General Public License 3
 */
class CFile
    extends BContent 
    implements 
        ISupportsSidebar, 
        IGlobalUniqueId
{
    const GUID = 'org.bambuscms.content.cfile';
    public function getGUID()
    {
        return self::GUID;
    }
    protected $RAWContent;
    private $_contentLoaded = false;
    
	/**
	 * @return CFile
	 */
	public static function Create($title)
	{
	    //FIXME RFiles::tempName(CFile)
	    
	    if(!is_uploaded_file($_FILES['CFile']['tmp_name']))
	    {
	        throw new XFileNotFoundException('no uploaded file', 'CFile');
	    }
	    $SCI = SContentIndex::alloc()->init();
	    list($dbid, $alias) = $SCI->createContent('CFile', $_FILES['CFile']['name']);
	    if(!move_uploaded_file($_FILES['CFile']['tmp_name'], '/Content/CFile/'.$dbid.'.data'))
	    {
	        throw new XUndefinedException('upload not moveable');
	    }
	    DFileSystem::SaveData('/Content/CFile/'.$dbid.'.meta',array(
	        'filename' => $_FILES['CFile']['name'], 
	        'type' => $_FILES['CFile']['type'],
	        'md5' => md5_file('/Content/CFile/'.$dbid.'.data',false),
	        'size' => $_FILES['CFile']['size']
        ));
	    $file = new CFile($alias);
	    new EContentCreatedEvent($file, $file);
	    return $file;
	}
	
	public static function Delete($alias)
	{
	    $SCI = SContentIndex::alloc()->init();
	    return $SCI->deleteContent($alias, 'CFile');
	}
	
	public static function Exists($alias)
	{
	    $SCI = SContentIndex::alloc()->init();
	    return $SCI->exists($alias, 'CFile');
	}
	
	/**
	 * [alias => [title, pubdate]]
	 * @return array
	 */
	public static function Index()
	{
	    $SCI = SContentIndex::alloc()->init();
	    return $SCI->getIndex('CFile', false);
	}
		
	public static function Open($alias)
	{
	    $SCI = SContentIndex::alloc()->init();
	    if($SCI->exists($alias, 'CFile'))
	    {
	        return new CFile($alias);
	    }
	    else
	    {
	        throw new XUndefinedIndexException($alias);
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
	}
	
	/**
	 * @return string
	 */
	public function getContent()
	{
        return sprintf('<div class="CFile"><a href="bin.php?file=%s">%s</a></div>', $this->getAlias(), $this->getTitle());
	}
	
	public function setContent($value)
	{
	    throw new XPermissionDeniedException('compiled templates are read only');
	}
	
	public function setRAWContent($value)
	{
	    //save and compile
		$this->Size = strlen($value);
		$this->_contentLoaded = true;
		$this->RAWContent = $value;
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
	
	//ISupportsSidebar
	public function wantsWidgetsOfCategory($category)
	{
		return in_array(strtolower($category), array('binary', 'data', 'settings', 'information', 'search'));
	}
}
?>