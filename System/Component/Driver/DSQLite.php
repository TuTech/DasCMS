<?php
/**
 * @package Bambus
 * @subpackage Drivers
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 28.03.2008
 * @license GNU General Public License 3
 */
class DSQLite extends BDriver implements IShareable 
{
	const Class_Name = 'DSQLite';
	/**
	 * @var SContentIndex
	 */
	public static $sharedInstance = NULL;
	/**
	 * @var SQLiteDatabase
	 */
	private static $DB = null;
	/**
	 * @return DSQLite
	 */
	public static function alloc()
	{
		$class = self::Class_Name;
		if(self::$sharedInstance == NULL && $class != NULL)
		{
			self::$sharedInstance = new $class();
			
			if(!file_exists(self::$sharedInstance->StoragePath("bambus.sqlite")))
			{
				throw new Exception("Bambus database not initialized! ".self::$sharedInstance->StoragePath("bambus.sqlite")." run setup scripts");
			}
			self::$DB = new SQLiteDatabase(self::$sharedInstance->StoragePath("bambus.sqlite"), 0600, $err);
			if($err)
			{
				throw new Exception($err);
			}
			//if not exists tbl ContentIndex create if not exists (Manager, ContentIndex, Alias) 
		}
		return self::$sharedInstance;
	}
    /**
     * @return SQLiteDatabase
     */
    public function init()
    {
    	return self::$DB;
    }
}
?>