<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2009-03-09
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage Content
 */
class CTextBrick
    extends BContent 
    implements 
        Interface_Content,
        ISupportsSidebar, 
        IGlobalUniqueId,
        ISearchDirectives,
        Interface_XML_Atom_ProvidesInlineText 
{
    const GUID = 'org.bambuscms.content.ctextbrick';
    const CLASS_NAME = 'CTextBrick';
    public function getClassGUID()
    {
        return self::GUID;
    }
    protected $RAWContent;
    private $_contentLoaded = false;
    
	/**
	 * @return CTextBrick
	 */
	public static function Create($title)
	{
	    list($dbid, $alias) = QBContent::create(self::CLASS_NAME, $title);
	    DFileSystem::Save(SPath::CONTENT.self::CLASS_NAME.'/'.$dbid.'.php', ' ');
	    DFileSystem::Save(SPath::CONTENT.self::CLASS_NAME.'/'.$dbid.'.html.php', ' ');
	    $tpl = new CTextBrick($alias);
	    new EContentCreatedEvent($tpl, $tpl);
	    return $tpl;
	}
	
	public static function Delete($alias)
	{
	    return parent::Delete($alias);
	}
		
	/**
	 * @param string $alias
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
	    return CTextBrick::defaultIcon();
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
        return $this->getContent();
    }
	//end Interface_XML_Atom_ProvidesInlineText
		
	
	public function setContent($value)
	{
	    throw new XPermissionDeniedException('can\'t set html content');
	}
	
	public function setRAWContent($value)
	{
	    //save and compile
		$this->Size = strlen($value);
		$this->_contentLoaded = true;
		$this->RAWContent = $value;
		$this->Content = $this->generateHTML($this->RAWContent);
		$len = 420;
		$desc = (mb_strlen($value,CHARSET) > $len) ? mb_substr($value,0,$len,CHARSET).'...' : $value;
		$this->Description = $this->generateHTML($desc);
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
		parent::Save();
	}
	
	protected function generateHTML($text)
	{
        preg_replace('/\r?\n/', "\n", $text);
        $lines = explode("\n", $text);
        $html = '';
        $par = '';
        $first = true;
        foreach ($lines as $line) 
        {
            $line = trim($line);
            if(empty($line))
            {
                $html .= ($par != '' ) 
                    ? sprintf("<p>%s</p>\n", $par)
                    : "<br />\n";
                $par = '';
            }
            else
            {
                //urls with title
                $line = preg_replace('/(^|[\s>\*%\-])([a-zA-Z0-9]+:\/\/[^\s{]+)({([^}]+)})/ui', '$1<a href="$2" target="_blank">$4</a>', $line);
                //urls
                $line = preg_replace('/(^|[\s>\*%\-])([a-zA-Z0-9]+:\/\/[^\s]+)/ui', '$1<a href="$2" target="_blank">$2</a>', $line);
                //internal links
                $line = preg_replace('/(^|[\s>\*%\-])(@[^@]+@)({([^}]+)})/mui', "$1<span title=\"$2\" class=\"cmsLink\">$2 <i>$4</i></span>", $line);
                $line = preg_replace('/(^|[\s>\*%\-])(@[^@]+@)$/mui', "$1<span title=\"$2\" class=\"cmsLink broken\">$2 <u>$3</u></span>", $line);
                //bold
                $line = preg_replace('/\*([^\*]+)\*/u', '<b>$1</b>', $line);
                //italic
                $line = preg_replace('/%([^%]+)%/u', '<i>$1</i>', $line);
                //[stuff]
                $line = preg_replace('/^\[(root\/tutech.tutech.net\/(.*)\/jpegs\/([^\.]+)(\.jpg|))\]$/mu', "<span title=\"$1\" class=\"cmsEmbed\"><img src=\"img/$2/$3.jpg\" alt=\"$1\" title=\"$1\" /></span>", $line);
               
                //lists
                $line = preg_replace('/^(--)(.*)$/mu', "<ul><ul><li>$2</li></ul></ul>", $line);
                $line = preg_replace('/^(-|•)(.*)$/mu', "<ul><li>$2</li></ul>", $line);
                //{stuff}
                $line = preg_replace('/^{(.*)}$/mu', "<span title=\"$1\" class=\"cmsRef\">$1</span>", $line);
                //line break
                $line = $first ? sprintf('<span class="firstLine">%s</span>', $line) : $line;
                $par .= $line.(substr($line,0,4) == '<ul>' ? '' : "<br />\n");
                $first = false;
            }
        }
        if(!empty($par))
        {
            $html .= sprintf("\t<p>%s</p>\n", $par);
        }
        $html = preg_replace('/<\/ul><\/ul><ul><ul>/mu', "", $html);
        $html = preg_replace('/<\/ul><ul>/mu', "", $html);
        $html = preg_replace('/(<\/?ul>)/mu', "\n$1\n", $html);
        $html = preg_replace('/<\/li><li>/mu', "</li>\n<li>", $html);
        return $html;
	}
	
	public function setDescription($value){}
	
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
        QBContent::setAllowSearchIndexing($this->getId(), !empty($allow));
    }
}
?>