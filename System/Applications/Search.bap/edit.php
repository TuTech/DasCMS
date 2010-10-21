<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author selke@tutech.de
 * @package org.bambuscms.applications.websites
 * @since 2006-10-11
 * @version 1.0
 */


$Search = SApplication::getControllerContent();
if($Search instanceof CSearch)
{
    echo new View_UIElement_ContentTitle($Search);

    $tbl = new View_UIElement_Table(View_UIElement_Table::HEADING_TOP|View_UIElement_Table::HEADING_LEFT, 'settings');
    $tbl->setHeaderTranslation(true);
    $tbl->addRow(array(
        'description',
        'value'
    ));

	//child formatter
	$formatters = Controller_View::getInstance()->getStoredViews();
	$formatterOptions = array('' => ' - '.SLocalization::get('none').' - ');
		foreach ($formatters as $f){
			$formatterOptions[$f] = $f;
		}
    $tbl->addRow(array(
        'content_formatter',
        new View_UIElement_MultipleChoice(
            'content_formatter',
            $formatterOptions,
            $Search->getChildContentFormatter(),
            View_UIElement_MultipleChoice::SELECT,
            false
        )
    ));

    $tbl->render();
}
?>