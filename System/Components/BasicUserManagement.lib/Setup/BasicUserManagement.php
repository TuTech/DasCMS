<?php
class Setup_BasicUserManagement
	extends _Setup
	implements 
		Setup_ForDatabaseTables,
		Setup_ForDatabaseTableReferences,
		Setup_ForContentFolder
{

	public function validateInputData() {
		$v = $this->inputValueForKey('system.administratorPassword');
		if(empty($v) || strlen($v) < 7)
		{
			$this->reportError('system.administratorPassword', 'password to short - minimum length for the administrator password is 7 characters');
		}
		if(count(array_unique(str_split($v))) < 3)
		{
			$this->reportError('system.administratorPassword', 'you should use at least 3 different characters');
		}
		return $this->getReport();
	}

	public function runContentFolderSetup() {
		DFileSystem::SaveData(
				$this->dirPath('configuration/groups.php'),
				array(
					"Administrator",
					"CMS",
					"Create",
					"Delete",
					"Edit",
					"Rename"
				)
		);
		$userData = 'a:1:{s:5:"admin";O:5:"SUser":11:{'.
			's:8:"password";s:32:"--PASSWORD--";'.
			's:8:"realName";s:5:"Admin";'.
			's:5:"email";s:12:"--EMAIL--";'.
			's:6:"groups";a:1:{i:0;s:13:"Administrator";}'.
			's:11:"permissions";a:0:{}'.
			's:10:"attributes";a:1:{s:7:"company";s:0:"";}'.
			's:12:"primaryGroup";s:0:"";'.
			's:22:"applicationPreferences";a:0:{}'.
			's:30:"applicationPreferenceKeyForces";a:0:{}'.
			's:27:"applicationPreferenceForces";a:0:{}'.
			's:16:"preferenceForced";b:0;}}';
		  $userData = str_replace('--PASSWORD--', md5($this->inputValueForKey('system.administratorPassword')), $userData);
		  $userData = str_replace('--EMAIL--', $this->inputValueForKey('system.webmasterEMail', ''), $userData);
		  DFileSystem::Save($this->dirPath('configuration/users.php'), DFileSystem::FHEADER.$userData);
	}

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