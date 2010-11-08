<?php
interface Import_Version1_Document
{
	public function getImportId();

	public function getType();

	public function getTitle();

	public function getSubTitle();

	public function getDescription();

	public function getContentEncoding();

	public function getContent();

	public function getPubDate();

	public function getRevokeDate();

	public function getCreateDate();

	public function getCreator();

	public function getModifyDate();

	public function getModifier();

	/**
	 * @return Import_Version1_References
	 */
	public function getReferences();
}
?>
