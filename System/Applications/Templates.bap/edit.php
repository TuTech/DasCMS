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
    echo new WContentTitle($Tpl);
    $editor = new WTextEditor($Tpl->RAWContent);
    $editor->disableSpellcheck();
    echo $editor;   
}
?>