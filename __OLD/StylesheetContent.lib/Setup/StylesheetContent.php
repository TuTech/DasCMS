<?php
class Setup_StylesheetContent
	extends _Setup
	implements
		Setup_ForContentFolder
{
	public function runContentFolderSetup() {
		$this->setupDir('CStylesheet');
	}
}
?>