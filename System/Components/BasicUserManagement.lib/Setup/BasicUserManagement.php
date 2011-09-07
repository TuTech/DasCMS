<?php

/*
internal helper class for setup only
*/
class Setup_BasicUserManagement_SUser{
	public 
		$password = "",
		$realName = "Admin",
		$email = "",
		$groups = array("Administrator"),
		$permissions = array(),
		$attributes = array("company" => ""),
		$primaryGroup = "",
		$applicationPreferences = array(),
		$applicationPreferenceKeyForces = array(),
		$applicationPreferenceForces = array(),
		$preferenceForced = false;
	
	public function __construct($email, $password){
		$this->email = $email;
		$this->password = md5($password);
	}
}

class Setup_BasicUserManagement
	extends _Setup
	implements 
		Setup_ForDatabaseTables,
		Setup_ForDatabaseTableReferences,
		Setup_ForContentFolder,
		Setup_ForConfiguration
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
		Core::FileSystem()->storeDataEncoded(
				$this->dirPath('configuration/groups.php'),
				array(
					"Administrator" => "",
					"CMS" => "",
					"Create" => "",
					"Delete" => "",
					"Edit" => "",
					"Rename" => ""
				)
		);
		$eml = $this->inputValueForKey('system.webmasterEMail', '');
		$userData = str_replace(':31:"Setup_BasicUserManagement_SUser"', ':5:"SUser"', serialize(array(
			"admin" => new Setup_BasicUserManagement_SUser($eml, $this->inputValueForKey('system.administratorPassword'))
		)));
		Core::FileSystem()->store($this->dirPath('configuration/users.php'), $userData);
	}

	public function runConfigurationSetup() {
		Core::Settings()->set("PAuthentication","SBambusSessionAuth");
		Core::Settings()->set("PAuthorisation","SBambusSessionAuth");
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