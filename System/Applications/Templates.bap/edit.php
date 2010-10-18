<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH 
 * @author selke@tutech.de
 * @package org.bambuscms.applications.templateeditor
 * @since 2006-10-11
 * @version 1.0
 */
$Tpl = SApplication::getControllerContent();
if(isset($Tpl) && $Tpl instanceof CTemplate)
{
    echo new View_UIElement_ContentTitle($Tpl);
    $editor = new View_UIElement_TextEditor($Tpl->RAWContent);
    $editor->disableSpellcheck();
    echo $editor;   
}
?>