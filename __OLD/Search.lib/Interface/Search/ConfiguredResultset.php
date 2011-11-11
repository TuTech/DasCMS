<?php
interface Interface_Search_ConfiguredResultset
{
	const ASC = 1;//DEFAULT
	const DESC = 2;

	/**
	 * @param int
	 * @return Interface_Search_ResultPage
	 */
	public function resultsFromPage($pageNr);

	/**
	 * @return Interface_Search_ConfiguredResultset
	 */
	public function inAscendingOrder();

	/**
	 * @return Interface_Search_ConfiguredResultset
	 */
	public function inDescendingOrder();

	/**
	 * @param int
	 * @return Interface_Search_ConfiguredResultset
	 */
	public function ordered($ascOrDesc);
}
?>
