<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH 
 * @author selke@tutech.de
 * @package org.bambuscms.applications.designeditor
 * @since 2009-05-15
 * @version 1.0
 */
$page = SApplication::getControllerContent();
if($page instanceof CPage)
{
    echo new WContentTitle($page);
    $editor = new WTextEditor($page->getContent());
    $editor->setWordWrap(false);
    $editor->disableSpellcheck();
	if(file_exists('System/External/Bespin')){
		$editor->addCssClass('bespin');
		$editor->addCustomAttribute("data-bespinoptions", '{ "stealFocus":true, "syntax": "html" }');
	}
    echo $editor;
}
?>