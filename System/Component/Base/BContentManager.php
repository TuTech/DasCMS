<?php
/**
 * @package Bambus
 * @subpackage BaseClasses
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 16.08.2007
 * @license GNU General Public License 3
 */
abstract class BContentManager extends BObject
{
	/**
	 * Must be properties of this class 
	 *
	 * @var array
	 */
	protected $Properties = array("Index", "Items");
	protected 
		$Index, // array id => title
		$Items //int num of items
		
		//handled in __get(), __set() & __isset()
		//ID as alias to open content
		//Title as alias for id array with exact matching titles
		;
	/**
	 * Can content be identified by it's title
	 */
	const UniqueTitles = true; 
		
	/**
	 * Forwarder for getter functions
	 *
	 * @param string $var
	 * @return mixed
	 * @throws XUndefinedIndexException
	 */
	public function __get($var)
	{
		if(method_exists($this, '_get_'.$var))
		{
			return $this->{'_get_'.$var}();	
		}
		else
		{
			throw new XUndefinedIndexException($var.' not in object');
		}
	}

	/**
	 * Forwarder for setter functions
	 *
	 * @param string $var
	 * @param mixed $value
	 * @return void
	 * @throws XPermissionDeniedException
	 */
	public function __set($var, $value)
	{
		if(method_exists($this, '_set_'.$var))
		{
			return $this->{'_set_'.$var}($value);	
		}
		else
		{
			throw new XPermissionDeniedException($var.' is read only');
		}
	}
	
	/**
	 * Chech existance of getter function for $var
	 *
	 * @param string $var
	 * @return boolean
	 */
	public function __isset($var)
	{
		return method_exists($this, '_get_'.$var);
	}
	
	public function _get_Index()
	{
		return $this->Index;
	}
	
	public function _get_Items()
	{
		return count($this->Index);
	}
	
	/**
	 * Create new content item
	 *
	 * @param string $title
	 * @return string id
	 */
	public abstract function Create($title);
	
	/**
	 * Delete content
	 *
	 * @param string $id
	 * @return bool
	 */
	public abstract function Delete($id);
	
	/**
	 * Check existance of id
	 *
	 * @param string $id
	 * @return bool
	 */
	public abstract function Exists($id);
	
	/**
	 * Open a content object
	 *
	 * @param string $id
	 * @return BContent|null
	 */
	public abstract function Open($id);
}
?>