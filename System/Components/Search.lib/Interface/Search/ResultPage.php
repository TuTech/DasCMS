<?php
interface Interface_Search_ResultPage
{
	/**
	 * @return array string[]
	 */
	public function asAliases();

	/**
	 * @return array Interface_Content[]
	 */
	public function asContents();

	/**
	 * get max page
	 * @return int
	 */
	public function getLastPageNumber();

	/**
	 * get the defined element count
	 * @return int
	 */
	public function getPageElementCount();

	/**
	 * get the item count for this page
	 * @return int
	 */
	public function getCurrentElementCount();

	/**
	 * get the item count for this page
	 * @return int
	 */
	public function getTotalElementCount();

	/**
	 * current page nr
	 * @return int
	 */
	public function getPageNumber();

	/**
	 * @return bool
	 */
	public function isFirstPage();

	/**
	 * @return bool
	 */
	public function isLastPage();
}
?>
