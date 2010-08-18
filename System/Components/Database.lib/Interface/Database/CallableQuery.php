<?php
interface Interface_Database_CallableQuery{
	/**
	 * @param string sql statement name
	 * @return Interface_Database_ConfigurableQuery
	 */
	public function call($function);
}

?>
