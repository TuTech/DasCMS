<?php
class Setup_Search
	extends _Setup
	implements Setup_ForDatabaseTables, Setup_ForDatabaseTableReferences
{
	public function runDatabaseTablesSetup() {
		$this->setupInDatabase('searchesTable');
		$this->setupInDatabase('searchResultsTable');
	}

	public function runDatabaseTableReferencesSetup() {
		$this->setupInDatabase('searchResultsReferences');
	}
}
?>
