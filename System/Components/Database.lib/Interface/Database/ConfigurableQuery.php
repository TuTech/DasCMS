<?php
interface Interface_Database_ConfigurableQuery{
	/**
	 * takes any number of parameters
	 * executes statement
	 * @return Interface_Database_FetchableQuery
	 */
	public function withParameters(/*...*/);

	/**
	 * executes statement
	 * @param array
	 * @return Interface_Database_FetchableQuery
	 */
	public function withParameterArray(array $parameters);

	/**
	 * executes statement
	 * @return Interface_Database_FetchableQuery
	 */
	public function withoutParameters();

	 /**
	  * provide an array for the results
	  * @return Interface_Database_ConfigurableQuery
	  */
	 public function useResultArray(&$array);
}

?>
