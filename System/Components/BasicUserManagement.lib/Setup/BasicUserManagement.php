<?php
class Setup_BasicUserManagement
	extends _Setup
	implements 
		Setup_ForDatabaseTables,
		Setup_ForDatabaseTableReferences
{
	public function runDatabaseTablesSetup() {
		$this->setupInDatabase(
				'groupsTable',
				'usersTable',
				'permissiontagsTable',
				'relUsersGroupsTable',
				'relPermissionTagsGroupsTable',
				'relPermissionTagsUsers'
			);
	}

	public function runDatabaseTableReferencesSetup() {
		$this->setupInDatabase(
				'permissionTagsReferences',
				'relPermissionTagsGroupsReferences',
				'relPermissionTagsUsersReferences',
				'relUsersGroupsReferences',
				'usersReferences'
			);
	}
}
?>