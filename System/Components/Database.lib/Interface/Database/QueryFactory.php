<?php
interface Interface_Database_QueryFactory{

	/**
	 * register sql statement
	 * public for on the fly built queries
	 * @param string $statementTemplate
	 * @param string $parameterDefinition
	 * @return void
	 */
	public function register($class, $name, $statementTemplate, $returnType, $parameterDefinition = '', $deterministic = false, $mutable = true);

	/**
	 * @param mixed $classNameOrObject
	 * @return Interface_Database_CallableQuery
	 */
	public function createQueryForClass($classNameOrObject);

	/**
	 * @return Interface_Database_QueryFactory
	 */
	public static function getInstance();
}
?>
