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
class CStylesheet
    extends BContent 
    implements 
        ISupportsSidebar, 
        IGlobalUniqueId,
        ISearchDirectives,
        IFileContent,
        Interface_XML_Atom_ProvidesInlineText,
        IHeaderService
{
    const GUID = 'org.bambuscms.content.cstylesheet';
    const CLASS_NAME = 'CStylesheet';
    public function getClassGUID()
    {
        return self::GUID;
    }
    protected $RAWContent;
    private $_contentLoaded = false;
    
	/**
	 * @return CStylesheet
	 */
	public static function Create($title)
	{
	    list($dbid, $alias) = BContent::createContent(self::CLASS_NAME, $title);
	    DFileSystem::Save(SPath::CONTENT.self::CLASS_NAME.'/'.$dbid.'.php', ' ');
	    DFileSystem::Save(SPath::CONTENT.self::CLASS_NAME.'/'.$dbid.'.html.php', ' ');
	    BContent::setMIMEType($alias, 'text/css');
	    $script = new CStylesheet($alias);
	    $e = new EContentCreatedEvent($script, $script);
	    return $script;
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
	    return CStylesheet::defaultIcon();
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
	
	protected function saveContentData()
	{
		//save content
		if($this->_contentLoaded)
		{
			DFileSystem::Save(SPath::CONTENT.self::CLASS_NAME.'/'.$this->Id.'.php',$this->RAWContent);
			DFileSystem::Save(SPath::CONTENT.self::CLASS_NAME.'/'.$this->Id.'.html.php',$this->Content);
		}
	}
	
	protected function generateHTML($text)
	{
        $text = htmlentities($text, ENT_QUOTES, CHARSET);
        $text = str_replace("\t", '    ', $text);
        $text = preg_replace("(\r\n|\r|\n)", "\t", $text);
        $text = preg_replace('/(^|})(.*?)({)/mui', '\\1<b>\\2</b>\\3', $text);
        $text = preg_replace('/({|;)(\s*)([a-z0-9\-]*?:)/mui', '\\1\\2<span>\\3</span>', $text);
        $text = preg_replace('/(\/\*.*?\*\/)/mui', '<i>\\1</i>', $text);
        $text = preg_replace('/(url\s*\(\s*)(.*?)(\s*\))/mui', '\\1<a href="\\2">\\2</a>\\3', $text);
        $text = preg_replace('/({[^}]*)(#([a-z0-9]{6}|[a-z0-9]{3}))/mui', '\\1&#35;\\3&nbsp;<span@@\\3">&nbsp;&nbsp;</span>', $text);
        $text = preg_replace('/<span@@([a-z0-9]{6}|[a-z0-9]{3})/mui', '<span class="color" style="background: #\\1', $text);
        $text = str_replace("\t", "\n", $text);
        $text = sprintf('<code class="%s" id="_%s">%s</code>', self::CLASS_NAME, $this->GUID, $text);
        return $text;
	}
	
	//IHeaderService
	public static function getHeaderServideItems($forAlias = null)
	{
	    return array('stylesheets' => Controller_Content::getInstance()->contentGUIDIndex(self::CLASS_NAME));
	}
	
	public static function sendHeaderService($embedGUID, EWillSendHeadersEvent $e)
	{
	    $url = 'file.php?get='.$embedGUID;
	    $e->getHeader()->addLink(CHARSET,$url,null,'text/css',null,'stylesheet',null,'all');
	}
	
	//IFileContent
	public function getFileName()
	{
	    return $this->getTitle();
	}
	
    public function getType()
    {
        return 'css';
    }
    
    public function getDownloadMetaData()
    {
        return array($this->getTitle().'.'.$this->getType(), $this->getMimeType(), null);
    }
    
    public function sendFileContent()
    {
        echo $this->getRAWContent();
    }
    
    public function getRawDataPath()
    {
        return null;
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
        BContent::setIndexingAllowed($this->getId(), !empty($allow));
    }
}
?>