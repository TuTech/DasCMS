<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH 
 * @author selke@tutech.de
 * @package org.bambuscms.applications.designeditor
 * @since 2009-07-21
 * @version 1.0
 */
$Calendar = SApplication::getControllerContent();
if($Calendar instanceof CCalendar)
{
    echo new WContentTitle($Calendar);
//    $editor = new WTextEditor($Script->RAWContent);
//    $editor->setWordWrap(false);
//    $editor->disableSpellcheck();
//    echo $editor;
}
?>