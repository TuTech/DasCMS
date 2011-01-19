<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH 
 * @author selke@tutech.de
 * @package org.bambuscms.applications.designeditor
 * @since 2009-05-15
 * @version 1.0
 */
$Script = SApplication::getControllerContent();
if($Script instanceof CScript)
{
    echo new View_UIElement_ContentTitle($Script);
    $editor = new View_UIElement_TextEditor($Script->getRAWContent());
    $editor->setWordWrap(false);
    $editor->disableSpellcheck();
	if(file_exists('System/External/Bespin')){
		$editor->addCssClass('bespin');
		$editor->setCodeAssist(false);
		$editor->addCustomAttribute("data-bespinoptions", '{ "stealFocus":true, "syntax": "js" }');
	}
    echo $editor;
}
?>