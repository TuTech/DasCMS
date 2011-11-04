<?php
class Database
{
	private static $modules = array();
	private $queries = array();
	
	public static function module($module){
		if(!self::$modules[$module]){
			self::$modules[$module] = new Database($module);
		}
		return self::$modules[$module];
	}
	
	private function __construct($module) {
		//read module definition for current db-backend
	}
	
	private function __clone() {}
	
	public function __call($name, $arguments) {
		if(!$this->queries[$name]){
			throw new Exception("query not found");
		}
		return new DatabaseQuery($this->queries[$name], $arguments);
	}
}
?>