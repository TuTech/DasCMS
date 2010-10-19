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
}
?>
