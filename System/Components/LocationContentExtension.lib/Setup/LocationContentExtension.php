<?php
class Setup_LocationContentExtension
	extends _Setup
	implements
		Setup_ForDatabaseTables,
		Setup_ForDatabaseTableReferences
{
	public function runDatabaseTablesSetup() {
		$this->setupInDatabase(
				'locationsTable',
				'relContentsLocationsTable'
			);
	}

	public function runDatabaseTableReferencesSetup() {
		$this->setupInDatabase(
				'relContentsLocationsReferences'
			);
	}
}
?>