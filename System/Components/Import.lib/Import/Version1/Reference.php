<?php
interface Import_Version1_Reference
{
	const ALIAS = 'alias';
	const IMPORT_ID = 'importId';

	/**
	 * @return string
	 */
	public function getReferenceType();

	/**
	 * @return string
	 */
	public function getReferenceValue();

}
?>
