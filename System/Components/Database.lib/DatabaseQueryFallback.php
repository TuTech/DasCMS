<?php
class DatabaseQueryFallback implements Interface_Database_FetchableQuery
{
	const INITIALIZED = 0;
	const QUERIED = 1;
	const FINISHED = 2;
	
	protected 
		$sql,
		$result,
		$resultFields,
		$db,
		$state;

	/**
	 * init and set sql
	 * @param type $sqlQuery 
	 */
	public function __construct($sqlQuery, $returnFields) {
		$this->sql = $sqlQuery;
		$this->db = DSQL::getInstance();
		$this->state = self::INITIALIZED;
		$this->resultFields = $returnFields;
	}
	
	/**
	 * check state
	 * @param int $state
	 * @return bool 
	 */
	protected function isState($state){
		return $this->state == $state;
	}

	/**
	 * advance state
	 */
	protected function nextState(){
		$this->state++;
	}

	/////////
	//EXECUTE
	/////////

	/**
	 * execute, return affected rows
	 * @return int
	 */
	public function execute() {
		if(!$this->isState(self::INITIALIZED)){
			throw new Exception('multiple execution of same statement');
		}
		$this->result = $this->db->queryExecute($this->sql);
		$this->nextState();
		return $this->result;
	}
	
	/**
	 * execute insert, return last insert id
	 * @return int
	 */
	public function executeInsert() {
		$this->execute();
		return $this->getInsertID();
	}
	
	/**
	 * get last insert id
	 * @return int
	 */
	public function getInsertID() {
		return $this->db->lastInsertID();
	}
	
	///////
	//QUERY
	///////

	/**
	 * internal fetch function 
	 * @return DSQLResult 
	 */
	protected function getResult(){		
		if($this->isState(self::INITIALIZED)){
			$this->result = $this->db->query($this->sql, DSQL::NUM);
			$this->nextState();
		}
		//runs after init
		if($this->isState(self::QUERIED)){		
			if(!$this->result instanceof DSQLResult){
				throw new DatabaseException('database result not valid');
			}
		}
		else{
			throw new Exception('multiple execution of same statement');
		}
		return $this->result;
	}

	/**
	 * fetch as multi-dimensional array
	 * @return array
	 */
	public function fetchList() {
		$ret = array();
		$res = $this->getResult();
		if($this->resultFields == 1){
			while($retVal = $res->fetch()){
				$ret[] = $retVal[0];
			}
		}
		else{
			while($retVal = $res->fetch()){
				$ret[] = $retVal;
			}
		}
		$this->free();
		return $ret;
	}
	
	/**
	 * fetch result line
	 * @return array
	 */
	public function fetchResult() {
		return $this->getResult()->fetch();
	}
	
	/**
	 * get single result
	 * @return mixed
	 */
	public function fetchSingleValue() {
		$res = $this->fetchResult();
		$this->free();
		//first element of first line or null
		return ($res) ? array_shift($res) : null;
	}
	
	/**
	 * free database after plain fetchResult() calls
	 */
	public function free() {
		if($this->isState(self::QUERIED)){
			$this->getResult()->free();
			$this->nextState();
		}
	}
	
	/**
	 * get number of rows in result
	 * @return int
	 */
	public function getRows() {
		return $this->getResult()->getRowCount();
	}
}
?>