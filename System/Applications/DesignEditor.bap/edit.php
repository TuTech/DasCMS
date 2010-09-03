<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH 
 * @author selke@tutech.de
 * @package org.bambuscms.applications.designeditor
 * @since 2009-05-27
 * @version 1.0
 */
$Stylesheet = SApplication::getControllerContent();
if($Stylesheet instanceof CStylesheet)
{
    echo new WContentTitle($Stylesheet);
    $editor = new WTextEditor($Stylesheet->RAWContent);
    $editor->setWordWrap(false);
    $editor->disableSpellcheck();
	if(file_exists('System/External/Bespin')){
		$editor->addCssClass('bespin');
		$editor->setCodeAssist(false);
		$editor->addCustomAttribute("data-bespinoptions", '{ "stealFocus":true, "syntax": "css" }');
	}
    echo $editor;
}
?>