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
	protected static $_errors = array(
			 204 => 'No Content',
			 301 => 'Moved Permanently',
			 302 => 'Moved Temporarily',
			 303 => 'See Other',
			 400 => 'Bad Request',
			 401 => 'Unauthorized',
			 402 => 'Payment Required',
			 403 => 'Forbidden',
			 404 => 'Not Found',
			 405 => 'Method Not Allowed',
			 406 => 'Not Acceptable',
			 408 => 'Request Time-Out',
			 409 => 'Conflict',
			 410 => 'Gone',
			 412 => 'Precondition Failed',
			 413 => 'Request Entity Too Large',
			 414 => 'Request-URL Too Large',
			 415 => 'Unsupported Media Type',
			 501 => 'Not Implemented',
			 503 => 'Out of Resources'
			);
	
	public static function errdesc($code)
	{
		if(!array_key_exists($code, self::$_errors))
		{
			$code = 501;
		}
		return array($code => self::$_errors[$code]);
	}
			
	const UniqueTitles = true; 
		
	public function _get_Index()
	{
		return self::$_errors;
	}
	
	public function _get_Items()
	{
		return count(self::$_errors);
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
		return array_key_exists($id,self::$_errors);
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
	const Class_Name = 'MError';
	
	public static $sharedInstance = NULL;
	private static $initializedInstance = false;
	
	/**
	 * Enter description here...
	 *
	 * @return MError
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
     * @return MError
     */
    function init()
    {
    	return $this;
    }
	//end IShareable
}
?>