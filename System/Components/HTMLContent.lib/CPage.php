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
    extends _Content 
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
	    list($dbid, $alias) = _Content::createContent('CPage', $title);
	    Core::FileSystem()->store(Core::PATH_CONTENT.'CPage/'.$dbid.'.content.php', ' ');
	    _Content::setMIMEType($alias, 'text/html');
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
	 * @throws FileNotFoundException
	 * @throws Exception
	 * @throws InvalidDataException
	 */
	public function __construct($alias)
	{
	    try
	    {
	        $this->initBasicMetaFromDB($alias, self::CLASS_NAME);
	    }
	    catch (UndefinedIndexException $e)
	    {
	        throw new ArgumentException('content not found');
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
	 * @return View_UIElement_Icon
	 */
	public static function defaultIcon()
	{
	    return new View_UIElement_Icon('CPage', 'content', View_UIElement_Icon::LARGE, 'mimetype');
	}
	
	/**
	 * Icon for this object
	 * @return View_UIElement_Icon
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
				$this->Content = Core::FileSystem()->load(Core::PATH_CONTENT.'CPage/'.$this->Id.'.content.php');
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
			Core::FileSystem()->store(Core::PATH_CONTENT.'CPage/'.$this->Id.'.content.php',$this->Content);
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
	    return _Content::isIndexingAllowed($this->getId());
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
        _Content::setIndexingAllowed($this->getId(), !empty($allow));
    }
}
?>