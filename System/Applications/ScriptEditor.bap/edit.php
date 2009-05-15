<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH 
 * @author selke@tutech.de
 * @package org.bambuscms.applications.designeditor
 * @since 2009-05-15
 * @version 1.0
 */
$currentFile = SApplication::getControllerContent();
if(!empty($currentFile))
{
	printf(
		'<h2>%s</h2>'
	    ,htmlentities($currentFile, ENT_QUOTES, 'utf-8')
    );
    $controller = SApplication::appController();
    $fileContent = ($controller->getSavedContent() == null)
        ? DFileSystem::Load(SPath::SCRIPT.$currentFile)
        : $controller->getSavedContent();
	$editor = new WTextEditor($fileContent);
    $editor->disableSpellcheck();
    echo $editor;
}
?>