<?php
/**
 * Description of ${name}
 *
 * @author ${user}
 */
class DatabaseAdapter {

	//////////////
	//adapter life
	//////////////

	/**
	 * global statement index
	 * sql-hash => statement ref
	 * @var array
	 */
	private static $prepared = array();

	/**
	 * prepare for execution / register SQL
	 * @param string $sql
	 * @return string statement ID
	 */
	public function prepare($sql){
		$id = sha1($sql);
		if(array_key_exists($id, $this->prepared)){
			//@todo replace %PRFX% with configs db table prefix -- a config change MUST trigger a cache invalidate
			//@todo mysql prepare
			$ref = null;
			self::$prepared[$id] = $ref;
		}
		return $id;
	}

	public function forStatement($statementId){
		return new DatabaseAdapter($statementId);
	}

	////////////
	//query life
	////////////

	private $statement;
	private $parameters = array();
	private $deterministic = false;
	private $mutable = true;

	/**
	 * set parameters as array or function args
	 * @param DatabaseAdapter $params
	 */
	public function withParameters($params)
	{
		switch (func_num_args()){
			case 0: $this->parameters = array();
			case 1: $this->parameters = (is_array($params)) ? $params : array($params);
			default: $this->parameters = func_get_args();
		}
		foreach ($this->parameters as $k => $v){
			$this->parameters[$k] = strval($v);
		}
	}

	/**
	 * allow caching
	 * @return DatabaseAdapter
	 */
	public function asDeterministic()
	{
		$this->deterministic = true;
		return $this;
	}

	/**
	 * the query result changes because of nondeterministic SQL functions (e.g. UUID(), NOW())
	 * @return DatabaseAdapter
	 */
	public function asNonDeterministic()
	{
		$this->deterministic = false;
		return $this;
	}

	/**
	 * the query result might change over time, by data from the database (i.e. Contents.pubDate)
	 * @return DatabaseAdapter
	 */
	public function asMutable()
	{
		$this->mutable = true;
		return $this;
	}

	/**
	 * this query returns the same result, no matter how old the cache is
	 * the cache of this file will be invalidated by database modifications
	 * @return DatabaseAdapter
	 */
	public function asImmutable()
	{
		$this->mutable = false;
		return $this;
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
