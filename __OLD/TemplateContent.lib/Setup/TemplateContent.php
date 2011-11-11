<?php
class Setup_TemplateContent
	extends _Setup
	implements
		Setup_ForContentFolder
{
	public function runContentFolderSetup() {
		$this->setupDir('CTemplate');
	}
}
?>