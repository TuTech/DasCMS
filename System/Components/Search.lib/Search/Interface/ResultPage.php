<?php
interface Search_Interface_ResultPage
{
	/**
	 * @return array string[]
	 */
	public function asAliases();

	/**
	 * @return array Interface_Content[]
	 */
	public function asContents();
}
?>
