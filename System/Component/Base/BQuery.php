<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2008-03-20
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage BaseClasses
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
	        self::$_database = DSQL::getSharedInstance();
	    }
	    return self::$_database;
	}
}
?>