<?php
class Setup_ContentSupport
	extends _Setup
	implements
		Setup_ForDatabaseTables,
		Setup_ForDatabaseTableReferences,
		Setup_ForDatabaseContent
{
	public function runDatabaseTablesSetup() {
		$this->setupInDatabase(
				'accessLogTable',
				'aliasTable',
				'changesTable',
				'changedByUsersTable',
				'contentTable',
				'formattersTable',
				'mimetypesTable',
				'tagsTable',
				'tagScoresTable',
				'sporeviewsTable',
				'relContentsClassesChainedContentsTable',
				'relClassesChainedContentsTable',
				'relContentsTargetViews',
				'relContentsTagsTable',
				'relContentsFormattersTable'
			);
	}

	public function runDatabaseTableReferencesSetup() {
		$this->setupInDatabase(
				'accessLogReferences',
				'aliasesReferences',
				'changesReferences',
				'contentsReferences',
				'relContentsClassesChainedContentsReferences',
				'relClassesChainedContentsReferences',
				'relContentsFormattersReferences',
				'relContentsTagsReferences',
				'sporeViewsReferences',
				'relContentsTargetViewsReferences',
				'relTagsScoresReferences'
			);
	}

	public function runDatabaseContentSetup() {
		$this->setupInDatabase('mimetypesInit');
	}
}
?>