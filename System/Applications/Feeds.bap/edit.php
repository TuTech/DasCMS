<?php
/************************************************
* Bambus CMS 
* Created:     11. Okt 06
* License:     GNU GPL Version 2 or later (http://www.gnu.org/copyleft/gpl.html)
* Copyright:   Lutz Selke/TuTech Innovation GmbH 
* Description: css editor interface
************************************************/
//document title
echo '<div id="objectInspectorActiveFullBox">';
//editing allowed?
	
if(PAuthorisation::has('org.bambuscms.content.cfeed.change') && isset($Feed) && $Feed instanceof CFeed)
{
		
	printf('<input type="hidden" id="filename" size="30" name="filename" value="%s"/><h2>%s</h2>'
		, htmlentities($Feed->Title, ENT_QUOTES, 'UTF-8')
		, htmlentities($Feed->Title, ENT_QUOTES, 'UTF-8')
		);
}
$tbl = new WTable(WTable::HEADING_TOP|WTable::HEADING_LEFT, 'settings');
$tbl->setHeaderTranslation(true);
$tbl->addRow(array('description', 'value'));
$tbl->addRow(array('items_per_page', '<input type="text" name="items_per_page" value="" />'));
$tbl->addRow(array('max_number_of_pages', '<input type="text" name="items_per_page" value="" />'));
$tbl->addRow(array('filter_method', new WMultipleChoice('filter_method',array(
        'all' => 'unfiltered', 
        'match_all' => 'match_all_in_list',
        'match_any' => 'match_any_in_list',
        'match_none' => 'match_items_not_tagged_with_any_of_list'
	),'all',WMultipleChoice::SELECT)));
$tbl->addRow(array('filter', '<textarea name="items_per_page"></textarea>'));
$tbl->addRow(array('target_view', '<select><option>foo</option></select>'));
$tbl->addRow(array('sort_order', new WMultipleChoice('sort_order',array('ASC' => 'ascending', 'DESC' => 'descending'),'ASC',WMultipleChoice::RADIO)));
$tbl->addRow(array('sort_by', new WMultipleChoice('sort_by',array('title' => 'title', 'pubdate' => 'pubDate'),'ASC',WMultipleChoice::RADIO)));
$tbl->render();
/*
ItemsPerPage
MaxPages
Filter
FilterMethod
TargetView
SortOrder
SortBy
*/


echo LGui::endForm();
echo '</div>';
?>