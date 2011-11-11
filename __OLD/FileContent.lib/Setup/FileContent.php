<?php
class Setup_FileContent
	extends _Setup
	implements
		Setup_ForDatabaseTables,
		Setup_ForDatabaseTableReferences,
		Setup_ForContentFolder
{
	public function runContentFolderSetup() {
		$this->setupDir('CFile');
	}

	public function runDatabaseTablesSetup() {
		$this->setupInDatabase('fileAttributesTable');
	}

	public function runDatabaseTableReferencesSetup() {
		$this->setupInDatabase('fileAttributesReferences');
	}
}
?>