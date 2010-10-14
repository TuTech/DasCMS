<?php
interface Search_Interface_Resultset
{
	/**
	 * @param int
	 * @return int
	 */
	public function getPageCountFor($nrOfItems);

	/**
	 * @return int
	 */
	public function getResultCount();

	/**
	 * @return float
	 */
	public function getExecutionTime();

	/**
	 * @param int
	 * @return Search_Interface_ConfiguredResultset
	 */
	public function fetch($nrOfItems);
}
?>
