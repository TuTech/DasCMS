<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2008-09-05
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage BaseClasses
 */
abstract class BRequest extends BObject 
{
    public static function get($key){}
    
    public static function alter($key, $value){}
    
    public static function has($key){}
}
?>