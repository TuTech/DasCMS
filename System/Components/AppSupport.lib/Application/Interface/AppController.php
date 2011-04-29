<?php
interface Application_Interface_AppController extends IGlobalUniqueId
{
	public function getTitle();
	public function getIcon();
	public function getDescription();
	public function getEditor();
	public function getContentObjects();
}
?>