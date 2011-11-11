<?php
class DatabaseQuery
{
	public function __construct($sql, $args = array()){
		//parse sql
	}
	
	/**
	 * @return mixed
	 */
	public function getValue(){
		//value of first item in first row
	}

	/**
	 * @return DatabaseResult
	 */
	public function getResult(){
		//value of first item in first row in a result wrapper
	}
	
	/**
	 * @return DatabaseResultSet
	 */
	public function getResults(){
		//result set 
	}
}
?>