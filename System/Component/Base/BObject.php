<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2007-11-28
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage BaseClasses
 */
abstract class BObject
{
    private static $_classIndex = null;
    
	/**
	 * load a controller for given app id
	 *
	 * @param string $ID
	 * @return BObject
	 * @throws XPermissionDeniedException
	 * @throws XUndefinedException
	 */
	public static function InvokeObjectByID($ID)
	{
	    if(self::$_classIndex == null)
	    {
	        self::$_classIndex = QBObject::preloadClassLookup();
	    }
	    if(!array_key_exists($ID, self::$_classIndex))
	    {
	        throw new XUndefinedIndexException('class id not indexed');
	    }
	    return self::InvokeObjectByDynClass(self::$_classIndex[$ID]);
	}

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
	
	
	public final static function InvokeObjectByDynClass($class)
	{
		$object = null;
		if(class_exists($class, true))
		{
		    if(class_exists('SComponentIndex',false))
		    {
    		    if(SComponentIndex::getSharedInstance()->IsImplementation($class, 'IShareable'))
    		    {
    		        $object = call_user_func($class.'::getSharedInstance');
    		    }
    			else
    			{
    			    $object = new $class();
    			}
		    }
		    if($object === null || !is_object($object))
		    {
		        $temp = new $class();
			    //if SComponentIndex was not available, respect the interfaces will 
				$object = ($temp instanceof IShareable) ? $temp->getSharedInstance() : $temp;
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