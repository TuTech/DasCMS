<?php
/**
 * @package Bambus
 * @subpackage BaseClasses
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 28.11.2007
 * @license GNU General Public License 3
 */
abstract class BObject
{
	//do path resolve
	//local id --> cms id path
	//cms id path --> local id
	/**
	 * initialite property in $var with $dataArray[$var] if it exists or use a default value
	 * only works if the property is null
	 *
	 * @param string $var
	 * @param array $dataArray
	 * @param mixed $defaultValue
	 */
	protected function initPropertyValues($var,array &$dataArray, $defaultValue)
	{
		if($this->{$var} == null)
		{
			$this->{$var} = (array_key_exists($var, $dataArray))
				? $dataArray[$var]
				: $defaultValue;
		}
	}
	/**
	 * get input data
	 *
	 * @param string $key
	 * @param string $inputVarName get/post/cookie/session
	 * @param mixed $default return this if no data set
	 * @return mixed
	 */
	protected function getDataFromInput($key, $inputVarName,$default = '')
	{
		global $_GET,$_POST,$_COOKIE, $_SESSION,$_REQUEST;
		$inputVarName = strtolower($inputVarName);
		$inputVarName = (substr($inputVarName,0,1) == '_') ? substr($inputVarName,1) : $inputVarName;
		$encode = get_magic_quotes_gpc();
		switch($inputVarName)
		{
			case 'get':
				$array = &$_GET;
				break;
			case 'post':
				$array = &$_POST;
				break;
			case 'cookie':
				$array = &$_COOKIE;
				break;
			case 'session':
				$array = &$_SESSION;
				$encode = false;
				break;
			default:
				$array = &$_REQUEST;
				
		}
		return isset($array[$key]) ? ($encode ? stripslashes($array[$key]) 
											  : $array[$key]) 
								   : $default;
	}
	
	
	public final static function InvokeObjectByDynClass($class)
	{
		$object = null;
		if(class_exists($class, true))
		{
			$temp = new $class();
			if($temp instanceof IShareable)
			{
				$object = $temp->alloc()->init();
			}
			else
			{
				$object = $temp;
			}
		}
		return $object;
	}
	
	//objects are not serializeable by default
	public function __sleep()
	{
		return array();
	}
	/**
	 * Return path to a given file or just the path for files 
	 * if $file is not set or null 
	 *
	 * @param string $file
	 * @return string file system path
	 */
	public function StoragePath($file = null, $addSuffix = true)
	{
		$path = sprintf(
			"./Content/%s/"
			,get_class($this)
		);
		if($file != null)
		{
			$path .= ($addSuffix) ? $file.'.php' : $file;
		}
		return $path;
	}
}
?>