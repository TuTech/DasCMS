<?php
/**
 * @package Bambus
 * @subpackage ContentManagers
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 28.11.2007
 * @license GNU General Public License 3
 */
class MPageManager extends BContentManager implements IShareable
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

	/**
	 * id => title content index
	 *
	 * @var array
	 */
	private static $_index = null;
	
	private static $_exists = array();
	
	private static function loadIndex()
	{
		if(self::$_index == null)
		{
			self::alloc()->init();
			self::$_index = SContentIndex::alloc()->init()->getIndex(self::$sharedInstance); 
		}
	}
	
	public function _get_Index()
	{
		self::loadIndex();
		return self::$_index;
	}
	
	public function _get_Items()
	{
		self::loadIndex();
		return count(self::$_index);
	}
	
	public function _get_Ids()
	{
		self::loadIndex();
		return array_keys(self::$_index);
	}
	
	public function _get_Names()
	{
		self::loadIndex();
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
		self::loadIndex();
		return isset(self::$_index[$id]) 
			? self::$_index[$id] 
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
		self::loadIndex();
		self::$_index[$id] = $title;
	}
	
	
	/**
	 * Create new Page
	 *
	 * @param string $title
	 * @return CPage
	 */
	public function Create($title, $forcedid = null)
	{
		$page = new CPage($forcedid);
		$page->Title = $title;
		$page->Content = " ";
		return $page;
	}
	
	/**
	 * Delete Page 
	 *
	 * @param string $id
	 */
	public function Delete($id)
	{
		self::loadIndex();
		new EContentDeletedEvent($this, new CPage($id));
		unset(self::$_index[$id]);
		//@todo rm page
	}

	/**
	 * Check existance of $id
	 *
	 * @param string $id
	 * @return bool
	 */
	public function Exists($id)
	{
		if(array_key_exists($id, self::$_exists))
		{
			return self::$_exists[$id];
		}
		if(self::$_index == null)
		{
			self::$_exists[$id] = SContentIndex::exists(self::CLASS_NAME.':'.$id);
			return self::$_exists[$id];
		}
		return isset(self::$_index[$id]);
	}
		
	/**
	 * Open a new Page
	 *
	 * @param string $id
	 * @return CPage
	 */
	public function Open($id)
	{
		if($this->Exists($id))
		{
			return new CPage($id);
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
	const CLASS_NAME = 'MPageManager';
	
	public static $sharedInstance = NULL;
	private static $initializedInstance = false;
	
	/**
	 * @return MPageManager
	 */
	public static function alloc()
	{
		$class = self::CLASS_NAME;
		if(self::$sharedInstance == NULL && $class != NULL)
		{
			self::$sharedInstance = new $class();
		}
		return self::$sharedInstance;
	}
    /**
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