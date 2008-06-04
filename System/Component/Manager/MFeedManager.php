<?php
/**
 * @package Bambus
 * @subpackage ContentManagers
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 28.11.2007
 * @license GNU General Public License 3
 */
class MFeedManager extends BContentManager implements IShareable
{
	/**
	 * array ids
	 * @var array
	 */
	protected $Ids;
	/**
	 * array titles
	 * @var array
	 */
	protected $Names;

	public function _get_Index()
	{
		return self::$_index;
	}
	
	public function _get_Items()
	{
		return count(self::$_index);
	}
	
	public function _get_Ids()
	{
		return array_keys(self::$_index);
	}
	
	public function _get_Names()
	{
		return array_values(self::$_index);
	}
	
	public function __destruct()
	{
		if(count(self::$_alterIndex) > 0)
		{
			try
			{
				DFileSystem::UpdateData($this->StoragePath("index"), self::$_alterIndex);
			} 
			catch(Exception $e)
			{
				echo $e->getMessage();
			}
		}
	}
	
	
	/**
	 * id => title content index
	 *
	 * @var array
	 */
	private static $_index = array();
	
	/**
	 * items to be changed on __destruct
	 *
	 * @var array
	 */
	private static $_alterIndex = array();
	
	/**
	 * Get tile for id
	 *
	 * @param string $id
	 * @return string|null
	 */
	public static function Index($id)
	{
		$saved = &self::$_index;
		$alter = &self::$_alterIndex;
		return isset($alter[$id])
			? $alter[$id]
			: isset($saved[$id])
				? $saved[$id]
				: null;
	}
	
	/**
	 * Alter an enty in the index
	 *
	 * @param string $id
	 * @param string $title
	 */
	public static function ChangeIndex($id, $title)
	{
		self::$_alterIndex[$id] = $title;
		self::$_index[$id] = $title;
	}
	
	
	/**
	 * Create new Feed
	 *
	 * @param string $title
	 * @return CFeed
	 */
	public function Create($title, $forcedid = null)
	{
		$feed = new CFeed($forcedid);
		$feed->Title = $title;
		return $feed;
	}
	
	/**
	 * Delete feed 
	 *
	 * @param string $id
	 */
	public function Delete($id)
	{
		new EContentDeletedEvent($this, new CFeed($id));
		unset(self::$_index[$id]);
		self::$_alterIndex[$id] = null;
		//@todo rm feed
	}

	/**
	 * Check existance of $id
	 *
	 * @param string $id
	 * @return bool
	 */
	public function Exists($id)
	{
		return isset(self::$_index[$id]);
	}
		
	/**
	 * Open a new feed
	 *
	 * @param string $id
	 * @return CFeed
	 */
	public function Open($id)
	{
		if($this->Exists($id))
		{
			$data = DFileSystem::LoadData($this->StoragePath($id));
			return @unserialize($data);
		}
		return null;
	}
		
	public function saveFeed(CFeed $feed)
	{
		if($this->Exists($feed->Id))
		{
			new EContentChangedEvent($this,$feed);
		}
		else
		{
			new EContentCreatedEvent($this,$feed);
		}
		$dat = serialize($feed);
		$this->ChangeIndex($feed->Id, $feed->Title);
		DFileSystem::SaveData($this->StoragePath($feed->Id), $dat);
	}
	
	/**
	 * Generate random id string
	 *
	 * @return string
	 */
	public function GenerateId()
	{
		$seed = rand().time();
		usleep(rand(0,100));
		$id = md5($seed.time());
		while($this->Exists($id))
		{
			$id = md5($id.rand());
		}
		return $id;
	}
	
	//begin IShareable
	const Class_Name = 'MFeedManager';
	
	public static $sharedInstance = NULL;
	private static $initializedInstance = false;
	
	/**
	 * Enter description here...
	 *
	 * @return MFeedManager
	 */
	public static function alloc()
	{
		$class = self::Class_Name;
		if(self::$sharedInstance == NULL && $class != NULL)
		{
			self::$sharedInstance = new $class();
		}
		return self::$sharedInstance;
	}
    /**
     * initialize whatever
     *
     * @return MPageManager
     */
    function init()
    {
    	if(!self::$initializedInstance)
    	{
    		try{
    			self::$_index = DFileSystem::LoadData($this->StoragePath("index"));
    		}
    		catch(XFileNotFoundException $e)
    		{
    			self::$_index = array();
    		}
    		self::$initializedInstance = true;
    	}
    	return $this;
    }
	//end IShareable
}
?>