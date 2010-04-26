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
abstract class BSystem extends BObject
{
	public static function ArrayApplyFunctionRecursive($array, $functionName)
	{
		if(is_array($array))
		{
			$keys = array_keys($array);
			for ($i = 0; $i < count($keys); $i++)
			{
				if(is_array($array[$keys[$i]]))
				{
					$array[$keys[$i]] = BSystem::ArrayApplyFunctionRecursive($array[$keys[$i]], $functionName);
				}
				else
				{
					$array[$keys[$i]] = call_user_func($functionName, $array[$keys[$i]]);
				}
			}
		}
		return $array;
	}
}
?>