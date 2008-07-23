<?php
/**
 * @package Bambus
 * @subpackage Drivers
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 28.03.2008
 * @license GNU General Public License 3
 */
class DSQL_MySQL extends DSQL  
{
	const Class_Name = 'DSQL_MySQL';
	
	/**
	 * @var mysqli
	 */
	private static $DB = null;
	
	private function translateMode($mode)
	{
		switch($mode)
		{
			case DSQL::NUM:
				return MYSQLI_NUM;
			case DSQL::ASSOC:
				return MYSQLI_ASSOC;
			case DSQL::BOTH:
				return MYSQLI_BOTH;
		}
		return null;
	}

	public function __construct()
	{
		if(self::$DB == null)
    	{
    		$cfg = Configuration::alloc()->init();
    		//$host $cfg->get()
    		self::$DB = new mysqli(
				$this->getCfgOrNull($cfg, 'db_server'),
				$this->getCfgOrNull($cfg, 'db_user'),
				$this->getCfgOrNull($cfg, 'db_password'),
				$this->getCfgOrNull($cfg, 'db_name'),
				$this->getCfgOrNull($cfg, 'db_port'),
				$this->getCfgOrNull($cfg, 'db_socket'));
			if(mysqli_connect_errno() != 0)
			{
				throw new XDatabaseException(mysqli_connect_error(), mysqli_connect_errno());
				self::$DB = null;
			}
    	}
	}
	
	private function getCfgOrNull(Configuration $cfg, $key)
	{
		$dat = $cfg->get($key);
		return empty($dat) ? null : $dat; 
	}
	
	/**
	 * @return DSQL
	 */
	public static function alloc()
	{
		throw new Exception('call DSQL::alloc() instead');
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
		return 'MySQL';
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
		if(count($values) == 0 || count($names) == 0)
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
		$sql = sprintf(
			"INSERT %sINTO %s (%s) VALUES ",
			$ignore ? 'IGNORE ' : '',
			$into,
			implode(', ', $names)
		);

		//build sql body
		$parts = array();
		foreach ($values as $valueBlock) 
		{
			if(count($valueBlock) != $expected)
			{
				throw new Exception(sprintf('number of values (%d) and number of names (%d) are different', count($values), count($names)));
			}
			$parts[] = '('.implode(', ', $valueBlock).')';
		}
		$sql .= implode(', ', $parts);
		return $this->queryExecute($sql);//FIXME
	}
	
	
	
	/**
	 * begin a new transaction if supported by database
	 * 
	 * @return boolean success
	 */
	public function beginTransaction()
	{
		self::$DB->autocommit(false);
		return true;
	}
	
	/**
	 * commit transaction 
	 * 
	 * @return boolean success
	 */
	public function commit()
	{
		self::$DB->commit();
		self::$DB->autocommit(true);
		return true;
	}
		
	/**
	 * rollback transaction
	 * 
	 * @return boolean success
	 */
	public function rollback()
	{
		self::$DB->rollback();
		self::$DB->autocommit(true);
		return true;
	}
	
	/**
	 * @return int
	 */
	public function lastInsertID()
	{
		return self::$DB->insert_id;
	}
	
	/**
	 * number of affected rows
	 * @return int
	 */
	public function affectedRows()
	{
		return self::$DB->affected_rows;
	}
	
	/**
	 * @return string
	 */
    public function escape($string)
    {
    	return self::$DB->real_escape_string($string);
    }

    /**
     * @return int affected rows
     */
    public function queryExecute($string)
    {
    	$res = self::$DB->query($string);
    	if(self::$DB->errno != 0)
    	{
    		throw new XDatabaseException(self::$DB->error, self::$DB->errno);
    	}
    	$succ = self::$DB->affected_rows;
    	if(is_object($res))
    	{
    		$res->free();
    	}
    	return $succ;
    }
       
    /**
     * @return DSQLResult
     */
    public function query($string, $mode = null)
    {
    	if ($mode != null) 
    	{
    		$res = self::$DB->query($string, $this->translateMode($mode));
    	}
    	else
    	{
    		$res = self::$DB->query($string);
    	}
    	if(self::$DB->errno != 0)
    	{
    		throw new XDatabaseException(self::$DB->error, self::$DB->errno);
    	}
    	return new DSQLResult_MySQL(self::$DB, $res);
    }
}
?>