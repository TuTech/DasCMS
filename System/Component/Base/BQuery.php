<?php
/**
 * @package Bambus
 * @subpackage BaseClasses
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 20.03.2008
 * @license GNU General Public License 3
 */
abstract class BQuery extends BObject
{
	/**
	 * @var DSQL
	 */
    protected static $_database = null;
	
	/**
	 * @return DSQL
	 */
	protected static function Database()
	{
	    if(self::$_database == null)
	    {
	        self::$_database = DSQL::alloc()->init();
	    }
	    return self::$_database;
	}
}
?>