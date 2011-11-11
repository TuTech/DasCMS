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

	private static function initIndex(){
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
	}

	/**
	 * load a controller for given app id
	 *
	 * @param string $id
	 * @return object
	 * @throws AccessDeniedException
	 * @throws Exception
	 */
	public static function InvokeObjectByID($id)
	{
	    return self::InvokeObjectByDynClass(self::resolveGUID($id));
	}

	public static function resolveGUID($id){
		self::initIndex();
		if(!array_key_exists($id, self::$_classIndex))
	    {
	        throw new UndefinedIndexException('class id not indexed');
	    }
		return self::$_classIndex[$id];
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