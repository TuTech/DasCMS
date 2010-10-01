<?php
interface Search_Interface_Parser{

	/**
	 * @return Search_Request
	 */
	public function parse($str);
}
?>
