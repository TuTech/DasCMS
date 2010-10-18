<?php
interface Search_Interface_ConfiguredResultset
{
	const ASC = 1;//DEFAULT
	const DESC = 2;

	/**
	 * @param int
	 * @return Search_Interface_ResultPage
	 */
	public function resultsFromPage($pageNr);

	/**
	 * @return Search_Interface_ConfiguredResultset
	 */
	public function inAscendingOrder();

	/**
	 * @return Search_Interface_ConfiguredResultset
	 */
	public function inDescendingOrder();

	/**
	 * @param int
	 * @return Search_Interface_ConfiguredResultset
	 */
	public function ordered($ascOrDesc);
}
?>
