<?php
/**
 * @package Bambus
 * @subpackage ContentManagers
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 28.11.2007
 * @license GNU General Public License 3
 */
class MFiles extends BContentManager implements IShareable
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
	
	private static $_index = null;
	
	protected function loadIndex()
	{
		//get items from db where manager like $this
		if(self::$_index == null)
		{
			self::$_index = SContentIndex::alloc()->init()->getIndex($this); 
		}
	} 
	
	public function _get_Index()
	{
		$this->loadIndex();
		return self::$_index;
	}
	
	public function _get_Items()
	{
		$this->loadIndex();
		return count(self::$_index);
	}
	
	public function _get_Ids()
	{
		$this->loadIndex();
		return array_keys(self::$_index);
	}
	
	public function _get_Names()
	{
		$this->loadIndex();
		return array_values(self::$_index);
	}
		
	/**
	 * Get tile for id
	 *
	 * @param string $id
	 * @return string|null
	 */
	public static function Index($id)
	{
		//return from self||db
	}
	
	/**
	 * Alter an enty in the index
	 *
	 * @param string $id
	 * @param string $title
	 */
	public static function ChangeIndex($id, $title)
	{
		//TODO update self and db index
	}
	
	
	/**
	 * Create new Feed
	 *
	 * @param string $title
	 * @return CFile
	 */
	public function Create($title, $forcedid = null)
	{
		if(is_uploaded_file($title))
		{
			$file = new CFile($title);
			return $file;
		}
		throw new XFileNotFoundException($title);
	}
	
	/**
	 * Delete feed 
	 *
	 * @param string $id
	 */
	public function Delete($id)
	{
		new EContentDeletedEvent($this, new CFile($id));
		//TODO remove from self index and from db
	}

	/**
	 * Check existance of $id
	 *
	 * @param string $id
	 * @return bool
	 */
	public function Exists($id)
	{
		//TODO lookup self index, lookup db
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
			return new CFile($id);
		}
		return null;
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
	const Class_Name = 'MFiles';
	
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
    		self::$initializedInstance = true;
    	}
    	return $this;
    }
	//end IShareable
}
?>