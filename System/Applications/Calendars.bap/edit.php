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

	$currentFormatter = $Calendar->getChildContentFormatter();
	$formatters = Formatter_Container::getFormatterList();
	$options = array(' - '.SLocalization::get('none').' - '  => '');
	foreach ($formatters as $f){
		$options[$f] = $f;
	}
	$selectHTML = "";
	foreach ($options as $title => $value){
		$selectHTML .= sprintf(
				'<option value="%s"%s>%s</option>',
				htmlentities($value, ENT_QUOTES, CHARSET),
				$value == $currentFormatter ? ' selected="selected"' : '',
				htmlentities($title, ENT_QUOTES, CHARSET)
				);
	}


    printf('<p><label>Formatter</label><select name="formatter">%s</select></p>', $selectHTML);
        
    $a = $Calendar->getContentAggregator();
    printf('<p><label>Aggregator</label><input type="text" name="aggregator" value="%s" /></p>', htmlentities($a, ENT_QUOTES, CHARSET));
}
?>