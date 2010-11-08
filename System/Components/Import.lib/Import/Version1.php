<?php
interface Import_Version1
{
	/**
	 * @return int
	 */
	public function getItemCount();

	/**
	 * @param int
	 * @return Import_Version1_Document
	 */
	public function getItem($number);
}
?>
