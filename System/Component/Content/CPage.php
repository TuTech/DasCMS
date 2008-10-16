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
	//init: load meta data
	//on access of content/text - load content data
	//on access of summary/description - load summary data
	const MANAGER = 'MPageManager';
	
	private 
		$_contentLoaded = false,
		$_summaryLoaded = false,
		$_created = false;

	/**
	 * @param string $id
	 * @throws XFileNotFoundException
	 * @throws XFileLockedException
	 * @throws XInvalidDataException
	 */
	public function __construct($id = null)
	{
		$manager = MPageManager::alloc()->init(); 
		$meta = array();
		$defaults = array(
			'CreateDate' => time(),
			'CreatedBy' => PAuthentication::getUserID(),
			'ModifyDate' => time(),
			'ModifiedBy' => PAuthentication::getUserID(),
			'PubDate' => 0,
			'Size' => 0,
			'Title' => 'new CPage '.date('r'),
		);
		if($id == null || !$manager->Exists($id))
		{
			//create
			$this->Id = ($id == null || strlen($id) != 32)
				? $manager->generateId()
				: $id;
			//saved in content 
			$this->Content = ' ';
			
			//saved in summary
			$this->Description = ' ';
			
			//save settings
			$this->_created = true;
			$this->_contentLoaded = true;
			$this->_summaryLoaded = true;
		}
		else
		{
			$this->Id = $id;
			$meta = SContentIndex::alloc()->init()->getMeta($this);
		}			
		foreach ($defaults as $var => $default) 
		{
			$this->initPropertyValues($var, $meta, $default);
		}
		$this->_origPubDate = $this->PubDate;
	}
	
	private function loadMyData($stream)//<id>.<stream>.php
	{
		switch($stream)
		{
			case 'content':
			case 'summary':
				return DFileSystem::Load($this->StoragePath($this->Id.'.'.$stream));
			default:
				throw new XUndefinedIndexException('CPage has no data file for '.$stream);
		}
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
				$this->Content = $this->loadMyData('content');
				$this->_contentLoaded = true;
			}
		}
		catch(Exception $e)
		{
			return $e->getMessage();
		}
		return $this->Content;
	}
	
	public function _get_Description()
	{
		try{
			if(!$this->_contentLoaded)
			{
				$this->Description = $this->loadMyData('summary');
				$this->_summaryLoaded = true;
			}
		}
		catch(Exception $e)
		{
			return $e->getMessage();
		}
		return $this->Description;
	}
	
	public function _set_Content($value)
	{
		$this->Content = $value;
		$this->Summary = $this->createSummary($value);
	}
	
	/**
	 * Get a list of all possible meta keys
	 *
	 * @return array
	 */
	public static function MetaKeys()
	{
		return array( 
			'Summary',
			'Text',
			'Title',
			'Content',
			'Alias',
			'PreviousAliases',
			'PubDate',
			'CreateDate',
			'ModifyDate',
			'ModifiedBy',
			'Source',
			'Id',
			'Tags'
		);
	}
	
	public function Save()
	{
		//count things changed
		$toSave = count($this->_data__set);
		
		//save content
		if(array_key_exists('Content', $this->_data__set) || $this->_created)
		{
			DFileSystem::Save($this->StoragePath($this->Id.'.content'),$this->Content);
			unset($this->_data__set['Content']);
		}
		
		//save description/summary
		if(array_key_exists('Description', $this->_data__set) || $this->_created)
		{
			DFileSystem::Save($this->StoragePath($this->Id.'.summary'),$this->Content);
			unset($this->_data__set['Description']);
		}
		MPageManager::ChangeIndex($this->Id, $this->Title);
		
		//fire events
		if($this->_created)//just created
		{
			new EContentCreatedEvent($this, $this);
			$this->_created = false;
		}
		elseif($toSave = count($this->_data__set))
		{
			new EContentChangedEvent($this, $this);
		}
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
	
	/**
	 * initialized MPageManager object
	 *
	 * @return MPageManager
	 */
	public function getManager()
	{
		return MPageManager::alloc()->init();
	}	
}
?>