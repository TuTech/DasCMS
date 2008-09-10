<?php
/**
 * @package Bambus
 * @subpackage ContentManagers
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 16.08.2007
 * @license GNU General Public License 3
 */
class MError extends BContentManager implements IShareable 
{
	public static function errdesc($code)
	{
		$code = SHTTPStatus::validate($code);
		$code = $code == null ? 501 : $code; 
		return array($code => SHTTPStatus::byCode($code, false));
	}
			
	const UniqueTitles = true; 
		
	public function _get_Index()
	{
		return SHTTPStatus::codes();
	}
	
	public function _get_Items()
	{
		return count(SHTTPStatus::codes());
	}
	
	/**
	 * Create new content item
	 *
	 * @param string $title
	 * @return string id
	 */
	public function Create($title){}
	
	/**
	 * Delete content
	 *
	 * @param string $id
	 * @return bool
	 */
	public function Delete($id){}
	
	/**
	 * Check existance of id
	 *
	 * @param string $id
	 * @return bool
	 */
	public function Exists($id)
	{
		return SHTTPStatus::validate($id) != null;
	}
	
	/**
	 * Open a content object
	 *
	 * @param string $id
	 * @return BContent|null
	 */
	public function Open($id)
	{
		if(!$this->Exists($id))
		{
			$id = 501;
		}
		return new CError($id);
	}
	//begin IShareable
	const CLASS_NAME = 'MError';
	
	public static $sharedInstance = NULL;
	private static $initializedInstance = false;
	
	/**
	 * Enter description here...
	 *
	 * @return MError
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
     * @return MError
     */
    function init()
    {
    	return $this;
    }
	//end IShareable
}
?>