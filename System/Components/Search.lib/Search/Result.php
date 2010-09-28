<?php
class Search_Result
{
	/*==Search_Query==
	 * -getPage(pageNo)
	 * -getAll()
	 * -getRunTime()
	 * -getItemCount()
	 * -getPagesCount()
	 */

	protected $searchId;

	public function __construct($searchId) {
		$this->searchId = $searchId;
		echo $searchId;
	}


	//before fetching check if query is finished, if not wait for 50ms and retry
}
?>
