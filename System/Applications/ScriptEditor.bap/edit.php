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
    echo new WContentTitle($Script);
    $editor = new WTextEditor($Script->RAWContent);
    $editor->setWordWrap(false);
    $editor->disableSpellcheck();
    echo $editor;
}
?>