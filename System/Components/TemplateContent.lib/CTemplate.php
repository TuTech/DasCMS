<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2008-10-17
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage Content
 */
class CTemplate 
    extends BContent 
    implements 
        ISupportsSidebar, 
        IGlobalUniqueId,
        IPageGenerator, 
        ISearchDirectives,
        Interface_XML_Atom_ProvidesInlineText 
{
    const GUID = 'org.bambuscms.content.ctemplate';
    const CLASS_NAME = 'CTemplate';
    
    public function getClassGUID()
    {
        return self::GUID;
    }
    protected $RAWContent;
    private $_contentLoaded = false;
    
	/**
	 * @return CTemplate
	 */
	public static function Create($title)
	{
	    list($dbid, $alias) = BContent::createContent('CTemplate', $title);
	    DFileSystem::save(SPath::TEMPLATES.$dbid.'.php', ' ');
	    $tpl = new CTemplate($alias);
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
	    return new WIcon('CTemplate', 'content', WIcon::LARGE, 'mimetype');
	}
	
	/**
	 * Icon for this object
	 * @return WIcon
	 */
	public function getIcon()
	{
	    return CTemplate::defaultIcon();
	}
	
	/**
	 * @return string
	 */
	public function getContent()
	{
	    //run template
	    try {
            $tpl = new TEngine($this->Id.'.php', BTemplate::CONTENT, array());
    		return $tpl->execute(array());
	    }
	    catch (Exception $e)
	    {
	        return '';
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
		
	public function generatePage(array $environment)
	{
	    try {
            $tpl = new TEngine($this->Id.'.php', BTemplate::CONTENT, Core::settings()->toArray());
    		return $tpl->execute($environment);
	    }
	    catch (Exception $e)
	    {
	        return '';
	    }
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
	
	public function getRAWContent()
	{
	    //load
	    if($this->RAWContent == null)
	    {
	        $this->RAWContent = DFileSystem::load(SPath::TEMPLATES.$this->Id.'.php');
	    }
	    return $this->RAWContent;
	}
	
	protected function saveContentData()
	{
		//save content
		if($this->_contentLoaded)
		{
			DFileSystem::save(SPath::TEMPLATES.$this->Id.'.php',$this->RAWContent);
			if(!empty($this->RAWContent))
			{
			    $tc = new TCompiler($this->Id.'.php', BTemplate::CONTENT);
			    $tc->save();
			}
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