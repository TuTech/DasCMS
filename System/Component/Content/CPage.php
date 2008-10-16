<?php
/**
 * @package Bambus
 * @subpackage Contents
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 28.11.2007
 * @license GNU General Public License 3
 */
class CPage extends BContent implements ISupportsSidebar 
{
	private $_contentLoaded = false;

	/**
	 * @return CPage
	 */
	public static function Create($title)
	{
	    $SCI = SContentIndex::alloc()->init();
	    list($dbid, $alias) = $SCI->createContent('CPage', $title);
	    DFileSystem::Save($this->StoragePath($dbid.'.content'),' ');
	    $page = new CPage($alias);
	    new EContentCreatedEvent($page, $$page);
	    return $page;
	}
	
	public static function Delete($alias)
	{
	    $SCI = SContentIndex::alloc()->init();
	    return $SCI->deleteContent($alias, 'CPage');
	}
	
	public static function Exists($alias)
	{
	    $SCI = SContentIndex::alloc()->init();
	    return $SCI->exists($alias, 'CPage');
	}
	
	/**
	 * [alias => [title, pubdate]]
	 * @return array
	 */
	public static function Index()
	{
	    $SCI = SContentIndex::alloc()->init();
	    return $SCI->getIndex('CPage', false);;
	}
		
	public static function Open($alias)
	{
	    $SCI = SContentIndex::alloc()->init();
	    if($SCI->exists($alias, 'CPage'))
	    {
	        return new CPage($alias);
	    }
	    else
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
	 * Enter description here...
	 *
	 * @return string
	 */
	public function _get_Content()
	{
		try{
			if(!$this->_contentLoaded)
			{
				$this->Content = DFileSystem::Load($this->StoragePath($this->Id.'.content'));
				$this->_contentLoaded = true;
			}
		}
		catch(Exception $e)
		{
			return $e->getMessage();
		}
		return $this->Content;
	}
	
	public function _set_Content($value)
	{
		$this->Content = $value;
		$this->Description = $this->createSummary($value);
	}
	
	public function Save()
	{
		//save content
		if($this->_contentLoaded)
		{
			DFileSystem::Save($this->StoragePath($this->Id.'.content'),$this->Content);
		}
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
}
?>