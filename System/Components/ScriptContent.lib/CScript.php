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
        Interface_XML_Atom_ProvidesInlineText,
        IHeaderService,
        IFileContent
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
	    list($dbid, $alias) = BContent::createContent(self::CLASS_NAME, $title);
	    DFileSystem::save(SPath::CONTENT.self::CLASS_NAME.'/'.$dbid.'.php', ' ');
	    DFileSystem::save(SPath::CONTENT.self::CLASS_NAME.'/'.$dbid.'.html.php', ' ');
	    BContent::setMIMEType($alias, 'application/javascript');
	    $script = new CScript($alias);
	    $e = new Event_ContentCreated($script, $script);
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
	 * @return View_UIElement_Icon
	 */
	public static function defaultIcon()
	{
	    return new View_UIElement_Icon(self::CLASS_NAME, 'content', View_UIElement_Icon::LARGE, 'mimetype');
	}

	/**
	 * Icon for this object
	 * @return View_UIElement_Icon
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
	        $this->Content = DFileSystem::load(SPath::CONTENT.self::CLASS_NAME.'/'.$this->Id.'.html.php');
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
	        $this->RAWContent = DFileSystem::load(SPath::CONTENT.self::CLASS_NAME.'/'.$this->Id.'.php');
	    }
	    return $this->RAWContent;
	}

	protected function saveContentData()
	{
		//save content
		if($this->_contentLoaded)
		{
			DFileSystem::save(SPath::CONTENT.self::CLASS_NAME.'/'.$this->Id.'.php',$this->RAWContent);
			DFileSystem::save(SPath::CONTENT.self::CLASS_NAME.'/'.$this->Id.'.html.php',$this->Content);
		}
	}

	protected function generateHTML($text)
	{
        $text = htmlentities($text, ENT_QUOTES, CHARSET);
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

	//IHeaderService
	public static function getHeaderServideItems($forAlias = null)
	{
	    return array('scripts' => Controller_Content::getInstance()->contentGUIDIndex(self::CLASS_NAME));
	}

	public static function sendHeaderService($embedAlias, Event_WillSendHeaders $e)
	{
	    $url = 'file.php?get='.$embedAlias;
	    $e->getHeader()->addScript('application/javascript',$url,'');
	}

	//IFileContent
	public function getFileName()
	{
	    return $this->getTitle();
	}

    public function getType()
    {
        return 'js';
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