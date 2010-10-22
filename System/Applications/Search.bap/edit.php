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

	//query string
	$tbl->addRow(array(
        'query_string',
        new View_UIElement_TextBox(
            'query_string',
            $Search->getQueryString(),
            View_UIElement_TextBox::MULTILINE
        )
    ));
	$tbl->addRow(array(
        'allow_extend_query_string',
		new View_UIElement_Confirm(
			'allow_extend_query_string',
			'allow_user_input_to_specify_query',
			$Search->getAllowExtendQueryString()
		)
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

	//items per page
	$tbl->addRow(array(
        'items_per_page',
        new View_UIElement_TextBox(
            'items_per_page',
            $Search->getItemsPerPage(),
            View_UIElement_TextBox::NUMERIC
        )
    ));

	//order
	$tbl->addRow(array(
        'order',
        new View_UIElement_MultipleChoice(
            'order',
            array(
                Interface_Search_ConfiguredResultset::ASC  => 'ascending',
                Interface_Search_ConfiguredResultset::DESC => 'descending'
    		),
            $Search->getOrder(),
            View_UIElement_MultipleChoice::RADIO
        )
    ));
	$tbl->addRow(array(
        'allow_overwrite_order',
		new View_UIElement_Confirm(
			'allow_overwrite_order',
			'allow_user_input_to_change_order',
			$Search->getAllowOverwriteOrder()
		)
    ));

	//target view
	$views = Controller_View_Content::activeSpores();
    $targetViews = array('' => SLocalization::get('do_not_link'));
    foreach ($views as $view)
    {
    	$targetViews[$view] = $view;
    }
    $tbl->addRow(array(
        'target_view',
        new View_UIElement_MultipleChoice(
            'target_view',
            $targetViews,
            $Search->getTargetView(),
            View_UIElement_MultipleChoice::SELECT,
            false
        )
    ));

    $tbl->render();
}
?>