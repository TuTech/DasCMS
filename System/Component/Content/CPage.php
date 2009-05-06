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
	    $page = new CPage($alias);
	    new EContentCreatedEvent($page, $page);
	    return $page;
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
	    if(!self::Exists($alias))
	    {
	        throw new XArgumentException('content not found');
	    }
	    $this->initBasicMetaFromDB($alias);
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
		$this->saveMetaToDB();
		new EContentChangedEvent($this, $this);
		if($this->_origPubDate != $this->PubDate)
		{
			$e = ($this->__get('PubDate') == 0)
				? new EContentRevokedEvent($this, $this)
				: new EContentPublishedEvent($this, $this);
		}
	}
	
	private function createSummary($of)
	{
		$begin = 0;
		//1. look for id="Teaser"
		$start = -1;
		$tag = '';
		
		$pos = mb_strpos($of, ' id="BCMSTeaser"',0, 'UTF-8');
		if($pos !== false)
		{
			$start = mb_strrpos(mb_substr($of,0,$pos, 'UTF-8'), '<','UTF-8');
			$stop = mb_strpos($of, ' ', $start, 'UTF-8');
			$tag = mb_substr($of,$start+1,$stop-$start-1, 'UTF-8');
		}
		else
		//2. look for first tag
		{
			$hits = preg_match('/<([^\/>\s]+)[^\/>]{0,}>/', $of, $matches);
			if($hits > 0)
			{
				$start = mb_strpos($of, '<'.$matches[1], 0, 'UTF-8');
				$tag = $matches[1];
			}
		}
		
		if($start >= 0)
		{
			$teaser = '';
			$tag = mb_strtolower($tag, 'UTF-8');
			$text = mb_strtolower($of, 'UTF-8');
			$len = mb_strlen($text, 'UTF-8');
			$offset = $start;
			$sps = 1;
			while($sps > 0 && $offset < $len)
			{
				//find next end
				$possibleEnd = mb_strpos($text,'</'.$tag.'>',$offset, 'UTF-8');
				//find more starting tags between start and end
				$substr = mb_substr($text, $offset+1, $possibleEnd-$offset, 'UTF-8');
				$psps = preg_match('/<'.$tag.'[^\/>]{0,}>/', $substr);
				//$psps is the number of other start tags found 
				$sps += $psps;
				//decrease sps because of the fond end point
				$offset = $possibleEnd+1;
				//decrease start positions count
				$sps--;
			}
			$textStart = mb_strpos($text,'>', $start, 'UTF-8')+1;// find end of teaser opening tag
			$textLength = $possibleEnd+$tag-$textStart;
			$res = mb_substr($of, $textStart, $textLength, 'UTF-8');
		}
		else
		//3. use the first 1024 chars
		{
			$res = strip_tags($of);
			if(mb_strlen($res, 'UTF-8') > 1024)
			{
				$searchRange = mb_substr($res, 990, 30);
				$pos = mb_strrpos($searchRange, ' ', 'UTF-8');
				$chopAt = ($pos !== false) ? 990 + $pos : 1020;
				$res = mb_substr($res, 0, $chopAt, 'UTF-8').'...';
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