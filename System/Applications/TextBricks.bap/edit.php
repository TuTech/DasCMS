<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH 
 * @author selke@tutech.de
 * @package org.bambuscms.applications.templateeditor
 * @since 2006-10-11
 * @version 1.0
 */
$Brick = SApplication::getControllerContent();
if($Brick instanceof CTextBrick)
{
    echo new WContentTitle($Brick);
    $editor = new WTextEditor($Brick->RAWContent);
    $editor->setWordWrap(false);
    echo $editor;
}
?>