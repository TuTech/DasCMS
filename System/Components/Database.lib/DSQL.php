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
abstract class DSQL
	extends BObject
    implements 
        Interface_Singleton
{
	private function __construct(){}
	const CLASS_NAME = 'DSQL';
	private static $engine = null;
	protected static $Database = null;
	protected static $Connector = null;
	protected static $tableNameTemplate = null;
	
	const NUM = 0;
	const ASSOC = 1;
	const BOTH = 2;
	
	/**
	 * @return DSQL
	 * @throws XDatabaseException
	 */
	public static function getInstance()
	{
		if(self::$Connector == null)
		{
			self::$engine = Core::settings()->get('db_engine');
			$connector = 'DSQL_'.self::$engine;
			if(!class_exists($connector, true))
			{
				throw new XDatabaseException('unsupported database '.self::$engine, 0);
			}
			self::$Connector = new $connector;
		}
		return self::$Connector;
	}
	
	public static function getEngineName()
	{
	    if(self::$engine == null)
	    {
	        self::$engine = Core::settings()->get('db_engine');
	    }
	    return self::$engine;
	}	
	
	public static function getEngines()
	{
	    //FIXME generate list from component index
	    return array('MySQL', 'SQLite');
	}
	
	/**
	 * FIXME pipe all table names through this function to handle table pre-/suffixing
	 * @param $name plain table name
	 * @return string table name in the database
	 */
	public static final function Table($name)
	{
	    if(self::$tableNameTemplate == null)
	    {
	        self::$tableNameTemplate = Core::settings()->getOrDefault('db_table_name_template','%s');
	    }
	    return sprintf(self::$tableNameTemplate, $name);
	}
	
	//allow config classes to be configured
	public function handleEventRequestingClassSettings(Event_RequestingClassSettings $e){}
	public function handleEventUpdateClassSettings(Event_UpdateClassSettings $e){}

	/**
	 * database name 
	 * 
	 * @return string
	 */
	abstract public function getEngine();

	/**
	 * insert wrapper without escape
	 *
	 * @param string $into table
	 * @param array $names field names
	 * @param array $values array with values or an array with arrays with values
	 * @param boolean $ignore default false
	 * @return int affected
	 */
	abstract public function insertUnescaped($into, array $names, array $values, $ignore = false);
	
	/**
	 * insert wrapper with escape
	 *
	 * @param string $into table
	 * @param array $names field names
	 * @param array $values array with values or an array with arrays with values
	 * @param boolean $ignore default false
	 * @return int affected
	 */
	public function insert($into, array $names, array $values, $ignore = false)
	{
		if(count($values) == 0)
		{
			throw new Exception('no data given to insert');
		}
		$into = $this->escape($into);
		$escapedNames = array();
		foreach ($names as $name) 
		{
			$escapedNames[] = $this->escape($name);
		}
		//init values 
		if(!is_array($values[0]))
		{
			$values = array($values);
		}
		$escapedValues = array();
		foreach ($values as $valueBlock) 
		{
			$block = array();
			foreach ($valueBlock as $value) 
			{
				$block[] = is_int($value) ? $value :  "'".$this->escape($value)."'";
			}
			$escapedValues[] = $block;
		}
		return $this->insertUnescaped($into,$escapedNames, $escapedValues, $ignore);
	}

	/**
	 * begin a new transaction if supported by database
	 * 
	 * @return boolean success
	 */
	abstract public function beginTransaction();
	
	/**
	 * commit transaction 
	 * 
	 * @return boolean success
	 */
	abstract public function commit();
	
	/**
	 * rollback transaction
	 * 
	 * @return boolean success
	 */
	abstract public function rollback();
	
	/**
	 * id of last inserted row
	 * 
	 * @return int
	 */
	abstract public function lastInsertID();
	
	/**
	 * number of affected rows
	 * 
	 * @return int
	 */
	abstract public function affectedRows();
	
	/**
	 * the best fitting escape function for this type of database
	 * 
	 * @param string $string
	 * @return string
	 */
	abstract public function escape($string);
		
	/**
	 * just dump data in the database
	 * 
	 * @param string $string 
	 * @return int affected rows
	 * @throws XDatabaseException
	 */
	abstract public function queryExecute($string);
		
	/**
	 * just dump data in the database
	 * 
	 * @param string $string 
	 * @param int $mode
	 * @return DSQLResult
	 * @throws XDatabaseException
	 */
	abstract public function query($string, $mode = null);

	/**
	 * @param string
	 * @return DSQLStatement
	 */
	abstract public function prepare($statement);
}
?>