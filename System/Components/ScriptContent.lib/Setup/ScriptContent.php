<?php
class Setup_ScriptContent
	extends _Setup
	implements
		Setup_ForContentFolder
{
	public function runContentFolderSetup() {
		$this->setupDir('CScript');
	}
}
?>