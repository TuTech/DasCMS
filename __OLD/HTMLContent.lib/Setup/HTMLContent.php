<?php
class Setup_HTMLContent
	extends _Setup
	implements
		Setup_ForContentFolder
{
	public function runContentFolderSetup() {
		$this->setupDir('CPage');
	}
}
?>