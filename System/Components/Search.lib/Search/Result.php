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

	protected $hash;

	public function __construct($hash) {
		$this->hash = $hash;
	}
	
}
?>
