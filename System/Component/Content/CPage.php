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
	    list($dbid, $alias) = QBContent::create('CPage', $title);
	    DFileSystem::Save(SPath::CONTENT.'CPage/'.$dbid.'.content.php', ' ');
	    QBContent::setMimeType($alias, 'text/html');
	    $page = new CPage($alias);
	    new EContentCreatedEvent($page, $page);
	    return $page;
	}
	
	public static function Delete($alias)
	{
	    return parent::Delete($alias);
	}
		
	public static function Open($alias)
	{
	    try
	    {
	        return new CPage($alias);
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
		if(empty($this->Description))
		{
		    $this->setDescription($this->createSummary($value));
		}
	}
	
	public function Save()
	{
		//save content
		if($this->_contentLoaded)
		{
			DFileSystem::Save(SPath::CONTENT.'CPage/'.$this->Id.'.content.php',$this->Content);
		}
		parent::Save();
	}
	
	private function createSummary($of)
	{
		$begin = 0;
		//1. look for id="Teaser"
		$start = -1;
		$tag = '';
		
		$pos = mb_strpos($of, ' id="BCMSTeaser"',0, CHARSET);
		if($pos !== false)
		{
			$start = mb_strrpos(mb_substr($of,0,$pos, CHARSET), '<',CHARSET);
			$stop = mb_strpos($of, ' ', $start, CHARSET);
			$tag = mb_substr($of,$start+1,$stop-$start-1, CHARSET);
		}
		else
		//2. look for first tag
		{
			$hits = preg_match('/<([^\/>\s]+)[^\/>]{0,}>/', $of, $matches);
			if($hits > 0)
			{
				$start = mb_strpos($of, '<'.$matches[1], 0, CHARSET);
				$tag = $matches[1];
			}
		}
		
		if($start >= 0)
		{
			$teaser = '';
			$tag = mb_strtolower($tag, CHARSET);
			$text = mb_strtolower($of, CHARSET);
			$len = mb_strlen($text, CHARSET);
			$offset = $start;
			$sps = 1;
			while($sps > 0 && $offset < $len)
			{
				//find next end
				$possibleEnd = mb_strpos($text,'</'.$tag.'>',$offset, CHARSET);
				//find more starting tags between start and end
				$substr = mb_substr($text, $offset+1, $possibleEnd-$offset, CHARSET);
				$psps = preg_match('/<'.$tag.'[^\/>]{0,}>/', $substr);
				//$psps is the number of other start tags found 
				$sps += $psps;
				//decrease sps because of the fond end point
				$offset = $possibleEnd+1;
				//decrease start positions count
				$sps--;
			}
			$textStart = mb_strpos($text,'>', $start, CHARSET)+1;// find end of teaser opening tag
			$textLength = $possibleEnd+$tag-$textStart;
			$res = mb_substr($of, $textStart, $textLength, CHARSET);
		}
		else
		//3. use the first 1024 chars
		{
			$res = strip_tags($of);
			if(mb_strlen($res, CHARSET) > 1024)
			{
				$searchRange = mb_substr($res, 990, 30);
				$pos = mb_strrpos($searchRange, ' ', CHARSET);
				$chopAt = ($pos !== false) ? 990 + $pos : 1020;
				$res = mb_substr($res, 0, $chopAt, CHARSET).'...';
			}
		}
		return $res;
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
        QBContent::setAllowSearchIndexing($this->getId(), !empty($allow));
    }
}
?>