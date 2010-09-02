<?php
class Setup_FeedContent
	extends _Setup
	implements 
		Setup_ForDatabaseTables,
		Setup_ForDatabaseTableReferences,
		Setup_ForContentFolder
{
	public function runContentFolderSetup() {
		$this->setupDir('CFeed');
	}

	public function runDatabaseTablesSetup() {
		$this->setupInDatabase(
				'feedsTable',
				'relFeedsContentsTable',
				'relFeedsTagsTable'
			);
	}

	public function runDatabaseTableReferencesSetup() {
		$this->setupInDatabase(
				'feedsReferences',
				'relFeedsContentsReferences',
				'relFeedsTagsReferences'
			);
	}
}
?>