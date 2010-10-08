<?php
class Setup_TreeNavigation
	extends _Setup
	implements
		Setup_ForContentFolder
{
	public function runContentFolderSetup() {
		$this->setupDir('NTreeNavigation');
		DFileSystem::saveData($this->dirPath('NTreeNavigation/index.php'), array());
	}
}
?>