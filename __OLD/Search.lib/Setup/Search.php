<?php
class Setup_Search
	extends _Setup
	implements Setup_ForDatabaseTables, Setup_ForDatabaseTableReferences, Setup_ForContentFolder
{
	public function runContentFolderSetup() {
		$this->setupDir('CSearch');
	}
	
	public function runDatabaseTablesSetup() {
		$this->setupInDatabase('searchesTable');
		$this->setupInDatabase('searchResultsTable');
	}

	public function runDatabaseTableReferencesSetup() {
		$this->setupInDatabase('searchResultsReferences');
	}
}
?>
