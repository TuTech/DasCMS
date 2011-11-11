<?php
class Setup_AuthenticationSupport
	extends _Setup
	implements Setup_ForDatabaseTables
{
	public function runDatabaseTablesSetup() {
		$this->setupInDatabase('authorisationLogTable');
	}
}
?>