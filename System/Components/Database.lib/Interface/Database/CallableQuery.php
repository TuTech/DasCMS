<?php
interface Interface_Database_CallableQuery{
	const SQL_STATEMENT = 0;
	const RETURN_FIELDS = 1;
	const PARAMETER_DEFINITION = 2;
	const IS_DETERMINISTIC = 3;

	/**
	 * @param string sql statement name
	 * @return Interface_Database_ConfigurableQuery
	 */
	public function call($function);

	/**
	 * @param string sql statement name
	 * @param array raw sql to inject
	 * @param array options for register
	 * @return Interface_Database_ConfigurableQuery
	 */
	public function buildAndCall($function, array $sqlInjections, array $newOptions = array());
}

?>
