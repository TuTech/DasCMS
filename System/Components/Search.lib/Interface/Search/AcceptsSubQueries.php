<?php
interface Interface_Search_AcceptsSubQueries
{
	/**
	 * narrow down results
	 * @param string $queryString
	 */
	public function addSubQuery($queryString);

	/**
	 * is this feature allowed for this content
	 * @bool
	 */
	public function isSubQueryingAllowed();
}
?>
