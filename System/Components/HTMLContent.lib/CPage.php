<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2007-11-28
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage Content
 */
class CPage 
    extends BContent 
    implements 
        ISupportsSidebar, 
        IGlobalUniqueId, 
        ISearchDirectives,
        Interface_XML_Atom_ProvidesInlineText 
{
    const GUID = 'org.bambuscms.content.cpage';
    const CLASS_NAME = 'CPage';
    
    public function getClassGUID()
    {
        return self::GUID;
    }
	private $_contentLoaded = false;

	/**
	 * @return CPage
	 */
	public static function Create($title)
	{
	    list($dbid, $alias) = BContent::createContent('CPage', $title);
	    DFileSystem::Save(SPath::CONTENT.'CPage/'.$dbid.'.content.php', ' ');
	    BContent::setMIMEType($alias, 'text/html');
	    $page = new CPage($alias);
	    new Event_ContentCreated($page, $page);
	    return $page;
	}
	
	public static function Delete($alias)
	{
	    return parent::Delete($alias);
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
	
	//Interface_XML_Atom_ProvidesInlineText
    public function getInlineTextType()
    {
        return 'html';
    }
    public function getInlineText()
    {
        return $this->getContent();
    }
	//end Interface_XML_Atom_ProvidesInlineText
	
	/**
	 * Icon for this filetype
	 * @return WIcon
	 */
	public static function defaultIcon()
	{
	    return new WIcon('CPage', 'content', WIcon::LARGE, 'mimetype');
	}
	
	/**
	 * Icon for this object
	 * @return WIcon
	 */
	public function getIcon()
	{
	    return CPage::defaultIcon();
	}
    
	/**
	 * @return string
	 */
	public function getContent()
	{
		try{
			if(!$this->_contentLoaded)
			{
				$this->Content = DFileSystem::Load(SPath::CONTENT.'CPage/'.$this->Id.'.content.php');
				$this->_contentLoaded = true;
			}
		}
		catch(Exception $e)
		{
			return $e->getMessage();
		}
		return $this->Content;
	}
	
	public function setContent($value)
	{
	    $this->_contentLoaded = true;
		$this->Content = $value;
		$this->Size = strlen($value);
	}
	
	protected function saveContentData()
	{
		//save content
		if($this->_contentLoaded)
		{
			DFileSystem::Save(SPath::CONTENT.'CPage/'.$this->Id.'.content.php',$this->Content);
		}
	}
	
	//ISupportsSidebar
	public function wantsWidgetsOfCategory($category)
	{
		return in_array(strtolower($category), array('text', 'media', 'settings', 'information', 'search'));
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
        BContent::setIndexingAllowed($this->getId(), !empty($allow));
    }
}
?>