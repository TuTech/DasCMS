<?php
interface Interface_Search_Resultset
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
	 * @return Interface_Search_ConfiguredResultset
	 */
	public function fetch($nrOfItems);
}
?>
