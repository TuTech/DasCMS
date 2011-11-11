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
class DSQL_MySQL extends DSQL  
{
	const CLASS_NAME = 'DSQL_MySQL';
	
	/**
	 * @var mysqli
	 */
	private static $DB = null;
	private static $configKeys = array(
	    'db_server' => 0,'db_port' => 0,'db_user' => 0,'db_password' => '######','db_name' => 0,
		'db_use_ssl' => 0, 'db_ssl_keyfile' => 0, 'db_ssl_certfile' => 0, 'db_ssl_cafile' => 0,
	);
	
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
			self::$DB = new mysqli();
            self::$DB->init();

			//ssl init
			if($this->getCfgOrNull('db_use_ssl')){
				self::$DB->ssl_set(
						$this->getCfgOrNull('db_ssl_keyfile'),
						$this->getCfgOrNull('db_ssl_certfile'),
						$this->getCfgOrNull('db_ssl_cafile'),
						$this->getCfgOrNull('db_ssl_capath'),
						$this->getCfgOrNull('db_ssl_cipher'));
			}

			//connect
			self::$DB->real_connect ($this->getCfgOrNull('db_server'),
				$this->getCfgOrNull('db_user'),
				$this->getCfgOrNull('db_password'),
				$this->getCfgOrNull('db_name'),
				$this->getCfgOrNull('db_port'),
				$this->getCfgOrNull('db_socket'));

			if(mysqli_connect_errno() != 0)
			{
				throw new DatabaseException(mysqli_connect_error(), mysqli_connect_errno());
				self::$DB = null;
			}
			$this->queryExecute("SET COLLATION_CONNECTION='utf8_unicode_ci', CHARACTER_SET_CLIENT='utf8',CHARACTER_SET_RESULTS='utf8';");
    	}
	}
	
	public function  __destruct()
	{
		if(self::$DB != null)
    	{
			self::$DB->close();
		}
	}
	
	private function getCfgOrNull($key)
	{
		$dat = Core::Settings()->get($key);
		return empty($dat) ? null : $dat; 
	}

	public function handleEventRequestingClassSettings(Event_RequestingClassSettings $e)
	{
	    $data = array();
        foreach (self::$configKeys as $mk => $altVal)
        {
			switch ($mk){
				case 'db_password':
					$type = Settings::TYPE_PASSWORD;
					break;
				case 'db_use_ssl':
					$type = Settings::TYPE_CHECKBOX;
					break;
				default :
					$type = Settings::TYPE_TEXT;
			}
            $data[$mk] = array($altVal === 0 ? Core::Settings()->get($mk) : $altVal,$type, null, $mk);
        }
        $e->addClassSettings($this, 'database', $data);
	}
	
	public function handleEventUpdateClassSettings(Event_UpdateClassSettings $e)
	{
	    $data = $e->getClassSettings($this);
	    foreach (self::$configKeys as $mk => $altVal)
        {
            if(isset($data[$mk]) && !($mk == 'db_password' && $data[$mk] == $altVal))
            {
                Core::Settings()->set($mk, $data[$mk]);
            }
        }
	}
	
	/**
	 * @return DSQL
	 */
	public static function getInstance()
	{
		throw new Exception('call DSQL::getInstance() instead');
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
			if(count($valueBlock) == 0)
			{
				continue;
			}
			if(count($valueBlock) != $expected)
			{
				throw new Exception(sprintf('number of values (%d) and number of names (%d) are different [%s] / [%s]', count($valueBlock), count($names), implode(', ', $valueBlock), implode(', ', $names)));
			}
			$parts[] = '('.implode(', ', $valueBlock).')';
		}
		if(count($parts) == 0)
		{
			throw new Exception('no data given');
		}
		$sql .= implode(', ', $parts);
		return $this->queryExecute($sql);
	}
	
	
	
	/**
	 * begin a new transaction if supported by database
	 * 
	 * @return boolean success
	 */
	public function beginTransaction()
	{
		$this->queryExecute('SET AUTOCOMMIT = 0');
		$this->queryExecute('START TRANSACTION');
		return true;
	}
	
	/**
	 * commit transaction 
	 * 
	 * @return boolean success
	 */
	public function commit()
	{
		$this->queryExecute('COMMIT');
		$this->queryExecute('SET AUTOCOMMIT = 1');
		return true;
	}
		
	/**
	 * rollback transaction
	 * 
	 * @return boolean success
	 */
	public function rollback()
	{
		$this->queryExecute('ROLLBACK');
		$this->queryExecute('SET AUTOCOMMIT = 1');
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

    
    private function stripParams($sql)
    {
        $sql = preg_replace('/(\'([^\']|[\\][\'])*\'|"([^"]|[\\]["])*")/', '?', $sql);
        $sql = preg_replace('/[\s\n\t\r]+/m', ' ', trim($sql));
        $sql = preg_replace('/ ([\d]+|0x[a-fA-F0-9]+)([\s]|[\)]|$)/i', ' ?\2', $sql);
        return preg_replace('/[\s\n\t\r]+/m', ' ', trim($sql));
    }
    
    /**
     * @return int affected rows
     */
    public function queryExecute($string)
    {
        $usql = $this->stripParams($string);
    	$res = self::$DB->query($string);
    	if(self::$DB->errno != 0)
    	{
    		throw new DatabaseException(self::$DB->error, self::$DB->errno, $string);
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
        $usql = $this->stripParams($string);
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
    		throw new DatabaseException(self::$DB->error, self::$DB->errno, $string);
    	}
    	if(!$res instanceof mysqli_result)
    	{
    	    throw new Exception($res);
    	}
    	return new DSQLResult_MySQL(self::$DB, $res);
    }

	/**
	 * @param string
	 * @return DSQLStatement
	 */
	public function prepare($statement)
	{
		$res = self::$DB->prepare($statement);
		if(!$res){
			throw new DatabaseException('prepare failed: '.self::$DB->error,self::$DB->errno,$statement);
		}
		return $res;
	}
}
?>