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
	    list($dbid, $alias) = QBContent::create('CTemplate', $title);
	    DFileSystem::Save(SPath::TEMPLATES.$dbid.'.php', ' ');
	    $tpl = new CTemplate($alias);
	    new EContentCreatedEvent($tpl, $tpl);
	    return $tpl;
	}
	
	public static function Delete($alias)
	{
	    return QBContent::deleteContent($alias);
	}
	
	public static function Exists($alias)
	{
	    return parent::contentExists($alias, self::CLASS_NAME);
	}
	
	/**
	 * [alias => [title, pubdate]]
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
	        return new CTemplate($alias);
	    }
	    catch (XArgumentException $e)
	    {
	        throw new XUndefinedIndexException($alias);
	    }
	}
	
	
	/**
	 * @param string $id
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
            $tpl = new TEngine($this->Id.'.php', BTemplate::CONTENT, LConfiguration::as_array());
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
	        $this->RAWContent = DFileSystem::Load(SPath::TEMPLATES.$this->Id.'.php');
	    }
	    return $this->RAWContent;
	}
	
	public function Save()
	{
		//save content
		if($this->_contentLoaded)
		{
			DFileSystem::Save(SPath::TEMPLATES.$this->Id.'.php',$this->RAWContent);
			if(!empty($this->RAWContent))
			{
			    $tc = new TCompiler($this->Id.'.php', BTemplate::CONTENT);
			    $tc->save();
			}
		}
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
		return in_array(strtolower($category), array('text', 'media', 'settings', 'information', 'search'));
	}
}
?>