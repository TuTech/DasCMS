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
	 * @return object
	 * @throws XPermissionDeniedException
	 * @throws XUndefinedException
	 */
	public static function InvokeObjectByID($ID)
	{
	    if(self::$_classIndex == null)
	    {
			self::$_classIndex = array();
			$d = Core::Database()
				->createQueryForClass('BObject')
				->call('load')
				->withoutParameters();
			while($row = $d->fetchResult()){
				self::$_classIndex[$row[0]] = $row[1];
			}
			$d->free();
	    }
	    if(!array_key_exists($ID, self::$_classIndex))
	    {
	        throw new XUndefinedIndexException('class id not indexed');
	    }
	    return self::InvokeObjectByDynClass(self::$_classIndex[$ID]);
	}

	public final static function InvokeObjectByDynClass($class)
	{
		$object = null;
		if(class_exists($class, true))
		{
			if(Core::isImplementation($class, 'Interface_Singleton'))
			{
				$object = call_user_func($class.'::getInstance');
			}
			else
			{
				$object = new $class();
			}
		}
		return $object;
	}

	//objects are not serializeable by default
	public function __sleep()
	{
		return array();
	}

}
?>