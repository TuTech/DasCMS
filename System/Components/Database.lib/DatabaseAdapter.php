<?php
class DatabaseAdapter
	implements Interface_Database_QueryFactory, Interface_Database_Query
{
	//@todo for cache class a config change MUST trigger a cache invalidate -- config changed event

	///////////////
	//main instance
	///////////////

	const SQL_STATEMENT = 0;
	const RETURN_FIELDS = 1;
	const PARAMETER_DEFINITION = 2;
	const IS_DETERMINISTIC = 3;
	const IS_MUTABLE = 4;

	protected static $register = array();
	protected static $statements = array();
	protected static $aliases = array();
	protected static $loaded = array();

	/**
	 * load sql queries for a class
	 * @param string $class
	 * @return void
	 */
	protected function loadDefinition($class)
	{
		if(!empty(self::$loaded[$class])){
			return;
		}
		self::$loaded[$class] = true;
		$file = sprintf('Content/SQLCache/%s/%s.json', Core::settings()->get('db_engine'), $class);
		if(file_exists($file)){
			$statements = Core::dataFromJSONFile($file);
			foreach ($statements as $name => $data){
				//s:sql, f:number of fields, p:parameter definition, d:deterministic, m:mutable
				$this->register($class, $name, $data['s'], $data['f'], $data['p'], $data['d'], $data['m']);
			}
		}
	}

	/**
	 * register sql statement
	 * public for on the fly built queries
	 * @param string $statementTemplate
	 * @param string $parameterDefinition
	 */
	public function register($class, $name, $statementTemplate, $returnFields, $parameterDefinition = '', $deterministic = false, $mutable = true)
	{
		$data = array($statementTemplate, $returnFields, $parameterDefinition, $deterministic, $mutable);
		$id = sha1(implode(':', $data));
		if(!array_key_exists($id, self::$register)){
			self::$register[$id] = $data;
		}
		$alias = $class.'::'.$name;
		if(!array_key_exists($alias, self::$aliases)){
			self::$aliases[$alias] = $id;
		}
	}

	/**
	 *
	 * @param mixed $classNameOrObject
	 * @param string $name
	 * @return DSQLStatement
	 */
	protected function getStatement($classNameOrObject, $name)
	{
		$class = (is_object($classNameOrObject)) ? get_class($classNameOrObject) : strval($classNameOrObject);
		$this->loadDefinition($class);

		$alias = $class.'::'.$name;
		if(!array_key_exists($alias, self::$aliases)){
			throw new XUndefinedIndexException('statement no registered');
		}
		$id = self::$aliases[$alias];
		if(!array_key_exists($id, self::$statements)){
			self::$statements[$id] = DSQL::getSharedInstance()->prepare(self::$register[$id][self::SQL_STATEMENT]);
		}
		return self::$statements[$id];
	}

	/**
	 * @param mixed $classNameOrObject
	 * @return DatabaseAdapter
	 */
	public function createQueryForClass($classNameOrObject)
	{
		$class = (is_object($classNameOrObject)) ? get_class($classNameOrObject) : strval($classNameOrObject);
		$self = clone $this;
		$self->class = $class;
		return $self;
	}

	////////////////////////
	//class adapter instance
	////////////////////////
	protected $class;
	protected $function;
	protected $parameters;
	protected $statement;
	protected $resultBindings;

	public function call($function)
	{
		if(!$this->class){
			return;
		}
		$this->function = $function;
		return $this;
	}

	public function withParameters(/*...*/)
	{
		if(!$this->class || !$this->function){
			return;
		}
		
		//get statement
		$this->statement = $this->getStatement($this->class, $this->function);

		//validate args
		$this->parameters = func_get_args();
		$id = self::$aliases[$this->class.'::'.$this->function];
		$parameterDefinition = &self::$register[$id][self::PARAMETER_DEFINITION];
			$this->prepareResultFields(&self::$register[$id][self::RETURN_FIELDS]);
		if(strlen($parameterDefinition) != count($this->parameters)){
			throw new XArgumentException('unexpected argument count');
		}

		//bind params
		for($i = 0; $i < strlen($parameterDefinition);$i++){
			$type = substr($parameterDefinition, $i,1);
			$this->statement->bind_param($type, $this->parameters[$i]);
		}
		$this->resultBindings = array();
		if(!$this->statement->execute()){
			throw new XDatabaseException("statement failed: ".$this->statement->error, $this->statement->errno, self::$register[$id][self::SQL_STATEMENT]);
		}
		return $this;
	}

	public function fetchSingleValue()
	{
		if(!is_object($this->statement)){
			throw new Exception('no statement to fetch from');
		}
		$res = '';
		$val = $this->statement->bind_result($res);
		$this->statement->fetch();
		$this->close();
		return $res;
	}

	public function useResultArray(&$array)
	{
		$this->resultBindings = &$array;
		return $this;
	}

	/**
	 * prepare the result array based on the sql meta data
	 * @param int $nrOfFields
	 */
	protected function prepareResultFields($nrOfFields)
	{
		if(!$this->statement){
			throw new Exception('nothing to fetch');
		}
		for($i = 0; $i < $nrOfFields; $i++){
			$this->resultBindings[$i] = '';
		}
		call_user_func_array(array($this->statement, "bind_result"), $this->resultBindings);
	}

	public function fetch()
	{
		if(!$this->statement){
			throw new Exception('nothing to fetch');
		}
		return $this->statement->fetch();
	}

	public function fetchResult()
	{
		$res = $this->fetch();
		if($res === false){
			throw new Exception('error while fetching query');
		}
		if($res === null){
			return null;
		}
		return $this->resultBindings;
	}

	public function getAffectedRows()
	{
		if($this->statement){
			return $this->statement->affected_rows;
		}
		return null;
	}

	public function getInsertID()
	{
		if($this->statement){
			return $this->statement->insert_id;
		}
		return null;
	}

	public function close()
	{
		if($this->statement){
			$this->statement->free_result();
			$this->statement = null;
		}
		$this->function = null;
		$this->parameters = null;
	}

	public function  __destruct() {
		$this->close();
	}


	/*
	$r=$dba->forClass(WImage)
 		->call(idToAlias)
 		->withParameters(eins, undZwei);

		->fetchValue();
		->expectFields(7);
		->expectNamedFields(array(one, two, three));
	while($r->fetch())...

	$r->close()

*/

	///////////
	//singleton
	///////////

	private static $mainInstance = null;
	
	private function __clone(){}

	private function __construct(){}

	public static function getInstance()
	{
		if(self::$mainInstance == null){
			self::$mainInstance = new DatabaseAdapter();
		}
		return self::$mainInstance;
	}
}
?>