<?php
class Setup_LinkContent
	extends _Setup
	implements
		Setup_ForContentFolder
{
	public function runContentFolderSetup() {
		$this->setupDir('CLink');
	}
}
?>