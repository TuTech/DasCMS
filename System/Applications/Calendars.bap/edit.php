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

    $f = $Calendar->getChildContentFormatter();
    printf('<p><label>Formatter</label><input type="text" name="formatter" value="%s" /></p>', htmlentities($f, ENT_QUOTES, CHARSET));
        
    $a = $Calendar->getContentAggregator();
    printf('<p><label>Aggregator</label><input type="text" name="aggregator" value="%s" /></p>', htmlentities($a, ENT_QUOTES, CHARSET));
}
?>