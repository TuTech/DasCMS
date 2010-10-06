<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2009-04-03
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage Content
 */
class CLink
    extends BContent 
    implements 
        ISupportsSidebar, 
        IGlobalUniqueId,
        ISearchDirectives,
        Interface_XML_Atom_ProvidesInlineText
{
    const GUID = 'org.bambuscms.content.clink';
    const CLASS_NAME = 'CLink';
    public function getClassGUID()
    {
        return self::GUID;
    }
    private $_contentLoaded = false;
    private $originalContent = null;
    
	/**
	 * @return CLink
	 */
	public static function Create($title)
	{
	    list($dbid, $alias) = BContent::createContent(self::CLASS_NAME, $title);
	    DFileSystem::Save(SPath::CONTENT.self::CLASS_NAME.'/'.$dbid.'.php', '');
	    $tpl = new CLink($alias);
	    new Event_ContentCreated($tpl, $tpl);
	    return $tpl;
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
	    return CLink::defaultIcon();
	}
	
	/**
	 * @return string
	 */

	public function getContent()
	{
	    if(!$this->_contentLoaded)
	    {
	        $this->Content = DFileSystem::Load(SPath::CONTENT.self::CLASS_NAME.'/'.$this->Id.'.php');
	        $this->originalContent = $this->Content;
	        $this->_contentLoaded = true;
	    }
	    return $this->Content;
	}
	
	public function setContent($value)
	{
	    $this->getContent();
	    $this->Content = $value;
	}
	
	protected function saveContentData()
	{
		//save content
		if($this->_contentLoaded)
		{
			DFileSystem::Save(SPath::CONTENT.self::CLASS_NAME.'/'.$this->Id.'.php',$this->Content);
		}
	}
	
	//Interface_XML_Atom_ProvidesInlineText
    public function getInlineTextType()
    {
        return 'html';
    }
    public function getInlineText()
    {
        //originalContent is in use because the content is altered on access
        return '<a href="'.htmlentities($this->originalContent, ENT_QUOTES, CHARSET).'">'.
                htmlentities($this->originalContent, ENT_QUOTES, CHARSET).'</a>';
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
	public function isSearchIndexingEditable()
    {
        return false;
    }
    public function changeSearchIndexingStatus($allow)
    {
    }
}
?>