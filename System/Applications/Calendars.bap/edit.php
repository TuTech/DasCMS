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
$f = $Calendar->getChildContentFormatter();
printf('<label>Formatter</label><input type="text" name="formatter" value="%s" />', htmlentities($f, ENT_QUOTES, CHARSET));
}
?>