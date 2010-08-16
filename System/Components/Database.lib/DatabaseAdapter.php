<?php
class DatabaseAdapter {
	//@todo for cache class a config change MUST trigger a cache invalidate -- config changed event

	//////////
	//register
	//////////

	protected static $register = array();
	protected static $statements = array();
	protected static $aliases = array();
	protected static $loaded = array();

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
				//s:sql,t:type,d:deterministic,m:mutable
				$this->register($class, $name, $data['s'], $data['t'], $data['d'], $data['m']);
			}
		}
	}

	/**
	 * register sql statement
	 * public for on the fly built queries
	 * @param string $statementTemplate
	 * @param string $parameterDefinition
	 */
	public function register($class, $name, $statementTemplate, $parameterDefinition = '', $deterministic = false, $mutable = true)
	{
		$data = array($statementTemplate, $parameterDefinition, $deterministic, $mutable);
		$id = sha1(implode(':', $data));
		if(!array_key_exists($id, self::$register)){
			self::$register[$id] = $data;
		}
		$alias = $class.'::'.$name;
		if(!array_key_exists($alias, self::$aliases)){
			self::$aliases[$alias] = $id;
		}
		return $id;
	}

	protected function getStatement($classNameOrObject, $name)
	{
		$class = (is_object($classNameOrObject)) ? get_class($classNameOrObject) : strval($classNameOrObject);
		$alias = $class.'::'.$name;
		$this->loadDefinition($class);

		if(!array_key_exists($alias, self::$aliases)){
			throw new XUndefinedIndexException('statement no registered');
		}
		$id = self::$aliases[$alias];
		if(!array_key_exists($id, self::$statements)){
			self::$statements[$id] = DSQL::getSharedInstance()->prepare(self::$register[$id]);
		}
		
		return self::$statements[$id];
	}

	/**
	 * returns the number oflines modified
	 * invalidates cache
	 * @return int
	 */
	public function executeModification(){
		//invalidate cache
		//query & return modified lines
	}

	/**
	 * no cache for now
	 * @return DSQLResult
	 */
	public function fetchLargeDataset(){}

	/**
	 * @return DSQLResult
	 */
	public function fetchSmallDataset(){}

	/**
	 * @return array
	 */
	public function fetchSingleLineQuery(){}

	/**
	 * @return string
	 */
	public function fetchSingleValueQuery(){}


	///////////
	//singleton
	///////////

	private static $mainInstance = null;

	private function   __clone() {	}

	private function  __construct($statement)
	{
		if($statement != null && !array_key_exists($statement, self::$prepared)){
			throw new XUndefinedIndexException("statement not found");
		}
		$this->statement = $statement;
	}

	public static function getInstance()
	{
		if(self::$mainInstance == null){
			self::$mainInstance = new DatabaseAdapter(null);
		}
		return self::$mainInstance;
	}
}
?>