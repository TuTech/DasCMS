<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2008-03-28
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage Drivers
 */
class DSQL_SQLite extends DSQL  
{
	const CLASS_NAME = 'DSQL_SQLite';
	
	/**
	 * @var SQLiteDatabase
	 */
	private static $DB = null;
	
	private function translateMode($mode)
	{
		switch($mode)
		{
			case DSQL::NUM:
				return SQLITE_NUM;
			case DSQL::ASSOC:
				return SQLITE_ASSOC;
			case DSQL::BOTH:
				return SQLITE_BOTH;
		}
		return null;
	}
	
	
	public function __construct()
	{
		if(self::$DB == null)
		{
			if(!file_exists($this->StoragePath("bambus.sqlite", false)))
			{
				throw new XDatabaseException("Bambus database not initialized! ".$this->StoragePath("bambus.sqlite", false)." run setup scripts",0);
			}
			self::$DB = new SQLiteDatabase($this->StoragePath("bambus.sqlite", false), 0600, $err);
			if($err)
			{
				self::$DB = null;
				throw new XDatabaseException($err,0);
			}
		}
	}
	
	/**
	 * @return DSQL
	 */
	public static function getSharedInstance()
	{
		throw new Exception('call DSQL::getSharedInstance() instead');
	}
	
    /**
     * @return DSQL
     */
    public function init()
    {
    	return $this;
    }
    
	/**
	 * @return string
	 */
	public function getEngine()
	{
		return 'SQLite';
	}
    
	/**
	 * insert wrapper without escape
	 *
	 * @param string $into table
	 * @param array $names field names
	 * @param array $values array with values or an array with arrays with values
	 * @param boolean $ignore default false
	 * @return int affected
	 */
	public function insertUnescaped($into, array $names, array $values, $ignore = false)
	{
		//sanity check
		if(count($values) == 0 || count($names) == 0 )
		{
			return true;
		}		
		$expected = count($names);
		
		//init values 
		if(!is_array($values[0]))
		{
			$values = array($values);
		}	
		
		//build sql head
		$sqlHead = sprintf(
			"INSERT %sINTO %s (%s) VALUES ",
			$ignore ? 'OR IGNORE ' : '',
			$into,
			implode(')(', $names)
		);

		//build sql body
		$parts = array();
		$affected = 0;
		foreach ($values as $valueBlock) 
		{
			if(count($valueBlock) != $expected)
			{
				throw new Exception('number of values and number of names are different');
			}
			$sqlBody = '('.implode(', ', $value).')';
			$affected += $this->queryExecute($sqlHead.$sqlBody);
		}
		return $affected;
	}

	/**
	 * begin a new transaction if supported by database
	 * 
	 * @return boolean success
	 */
	public function beginTransaction()
	{
		return self::$DB->queryExec("BEGIN TRANSACTION;");
	}
	
	/**
	 * commit transaction 
	 * 
	 * @return boolean success
	 */
	public function commit()
	{
		return self::$DB->queryExec('COMMIT;');
	}
		
	/**
	 * rollback transaction
	 * 
	 * @return boolean success
	 */
	public function rollback()
	{
		return self::$DB->queryExec('ROLLBACK;');
	}
	
	/**
	 * @return string
	 */
    public function escape($string)
    {
    	return sqlite_escape_string($string);
    }
    
	/**
	 * @return int
	 */
	public function lastInsertID()
	{
		return self::$DB->lastInsertRowid();
	}

	/**
	 * number of affected rows
	 * @return int
	 */
	public function affectedRows()
	{
		return self::$DB->changes();
	}
	
	/**
     * @return int affected rows
     */
    public function queryExecute($string)
    {
    	$succ = self::$DB->queryExec($string);
    	if(!$succ)
    	{
    		$eno = sqlite_last_error(self::$DB);
    		throw new XDatabaseException(sqlite_error_string($eno), $eno);
    	}
    	$result = self::$DB->changes();
    }
       
    /**
     * @return DSQLResult
     */
    public function query($string, $mode = null)
    {
    	$res = ($mode) 
    		? (self::$DB->query($string, $this->translateMode($mode)))
    		: (self::$DB->query($string));
    	if(!$res)
    	{
    		$eno = sqlite_last_error(self::$DB);
    		throw new XDatabaseException(sqlite_error_string($eno), $eno);
    	}
    	return new DSQLResult_SQLite(self::$DB, $res);
    }
}
?>