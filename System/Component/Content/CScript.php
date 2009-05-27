<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2009-05-29
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage Content
 */
class CScript
    extends BContent 
    implements 
        ISupportsSidebar, 
        IGlobalUniqueId,
        ISearchDirectives,
        Interface_XML_Atom_ProvidesInlineText 
{
    const GUID = 'org.bambuscms.content.cscript';
    const CLASS_NAME = 'CScript';
    public function getClassGUID()
    {
        return self::GUID;
    }
    protected $RAWContent;
    private $_contentLoaded = false;
    
	/**
	 * @return CScript
	 */
	public static function Create($title)
	{
	    list($dbid, $alias) = QBContent::create(self::CLASS_NAME, $title);
	    DFileSystem::Save(SPath::CONTENT.self::CLASS_NAME.'/'.$dbid.'.php', ' ');
	    DFileSystem::Save(SPath::CONTENT.self::CLASS_NAME.'/'.$dbid.'.html.php', ' ');
	    BContent::setMimeType($alias, 'text/javascript');
	    $script = new CScript($alias);
	    $e = new EContentCreatedEvent($script, $script);
	    return $script;
	}
	
	public static function Delete($alias)
	{
	    return parent::Delete($alias);
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
	        return new CScript($alias);
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
	    return new WIcon(self::CLASS_NAME, 'content', WIcon::LARGE, 'mimetype');
	}
	
	/**
	 * Icon for this object
	 * @return WIcon
	 */
	public function getIcon()
	{
	    return CScript::defaultIcon();
	}
	
	/**
	 * @return string
	 */
	public function getContent()
	{
	    if(!$this->_contentLoaded)
	    {
	        $this->Content = DFileSystem::Load(SPath::CONTENT.self::CLASS_NAME.'/'.$this->Id.'.html.php');
	        $this->_contentLoaded = true;
	    }
	    return $this->Content;
	}
	
	//Interface_XML_Atom_ProvidesInlineText
    public function getInlineTextType()
    {
        return 'html';
    }
    public function getInlineText()
    {
        return '<div style="white-space:pre">'.$this->getContent().'</div>';
    }
	//end Interface_XML_Atom_ProvidesInlineText
		
	
	public function setContent($value)
	{
	    throw new XPermissionDeniedException('can\'t set html content');
	}
	
	public function setRAWContent($value)
	{
	    //save and compile
		$this->_contentLoaded = true;
		$this->_modified = true;
	    $this->Size = strlen($value);
		$this->RAWContent = $value;
		$this->Content = $this->generateHTML($this->RAWContent);
	}
	
	public function getRAWContent()
	{
	    //load
	    if($this->RAWContent == null)
	    {
	        $this->RAWContent = DFileSystem::Load(SPath::CONTENT.self::CLASS_NAME.'/'.$this->Id.'.php');
	    }
	    return $this->RAWContent;
	}
	
	public function Save()
	{
		//save content
		if($this->_contentLoaded)
		{
			DFileSystem::Save(SPath::CONTENT.self::CLASS_NAME.'/'.$this->Id.'.php',$this->RAWContent);
			DFileSystem::Save(SPath::CONTENT.self::CLASS_NAME.'/'.$this->Id.'.html.php',$this->Content);
		}
		$this->saveMetaToDB();
		$e = new EContentChangedEvent($this, $this);
		if($this->_origPubDate != $this->PubDate)
		{
			$e = ($this->__get('PubDate') == 0)
				? new EContentRevokedEvent($this, $this)
				: new EContentPublishedEvent($this, $this);
		}
	}
	
	protected function generateHTML($text)
	{
        $text = htmlentities($text, ENT_QUOTES, 'UTF-8');
        $text = str_replace("\t", '    ', $text);
        $text = preg_replace("(\r\n|\r|\n)", "\t", $text);
        $r =
            'break|do|if|switch|typeof|case|else|in|this|var|'.
            'catch|false|instanceof|throw|void|continue|finally|new|true|while|'.
            'default|for|null|try|with|delete|function|return';
        $text = preg_replace('/\b('.$r.')\b/mui', '<b>\\1</b>', $text);
        $text = preg_replace('/(\/\*.*?\*\/)/mui', '<i>\\1</i>', $text);
        $text = preg_replace('/(\/\/[^\t]*)[\t]/mui', "<i>\\1</i>\t", $text);
        $text = str_replace("\t", "\n", $text);
        $text = sprintf('<code class="%s" id="_%s">%s</code>', self::CLASS_NAME, $this->GUID, $text);
        return $text;
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