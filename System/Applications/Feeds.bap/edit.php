<?php
/************************************************
* Bambus CMS 
* Created:     28. Okt 08
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
$tbl->addRow(array(
    'description', 
    'value'
));
$tbl->addRow(array(
    'items_per_page', 
    new WTextBox(
        'ItemsPerPage', 
        $Feed->option(CFeed::SETTINGS, 'ItemsPerPage'), 
        WTextBox::NUMERIC
    )
));
$tbl->addRow(array(
    'max_number_of_pages', 
    new WTextBox(
        'MaxPages', 
        $Feed->option(CFeed::SETTINGS, 'MaxPages'), 
        WTextBox::NUMERIC
    )
));
$tbl->addRow(array(
    'filter_method', 
    new WMultipleChoice(
        'FilterMethod',array(
            'all' => 'unfiltered', 
            'match_all' => 'match_all_in_list',
            'match_any' => 'match_any_in_list',
            'match_none' => 'match_items_not_tagged_with_any_of_list'
		),
        $Feed->option(CFeed::SETTINGS, 'FilterMethod'),
        WMultipleChoice::SELECT
    )
));
$tbl->addRow(array(
    'filter', 
    new WTextBox(
        'Filter', 
        implode(', ',$Feed->option(CFeed::SETTINGS, 'Filter')), 
        WTextBox::MULTILINE
    )
));
$views = QSpore::activeSpores();
$targetViews = array('' => SLocalization::get('do_not_link'));
foreach ($views as $view) 
{
	$targetViews[$view] = $view;
}
$tbl->addRow(array(
    'target_view', 
    new WMultipleChoice(
        'TargetView',
        $targetViews,
        $Feed->option(CFeed::SETTINGS, 'TargetView'),
        WMultipleChoice::SELECT, 
        false
    )
));
$tbl->addRow(array(
    'sort_order', 
    new WMultipleChoice(
        'SortOrder',array(
            'ASC' => 'ascending', 
            'DESC' => 'descending'
		),
        ($Feed->option(CFeed::SETTINGS, 'SortOrder') ? 'DESC' : 'ASC'),
        WMultipleChoice::RADIO
    )
));
$tbl->addRow(array(
    'sort_by', 
    new WMultipleChoice(
        'SortBy',array(
            'title' => 'title', 
            'pubdate' => 'pubDate'
		),
        $Feed->option(CFeed::SETTINGS, 'SortBy'),
        WMultipleChoice::RADIO
    )
));
$tbl->render();

//header 
$header = new WPropertyEditor('headerConfig', 'header');
$header->add('previous_link', 'previous_link', new WList(array(
    new WTextBox('header_caption_prev_link','',WTextBox::TEXT,'caption'))),
    true
);
$header->add('next_link', 'next_link', new WList(array(
    new WTextBox('header_caption_next_link','',WTextBox::TEXT,'caption'))),
    true
);
$header->add('page_no', 'page_no', new WList(array(
    new WConfirm('header_pagina_type','pagina_as_selection', false),
    new WTextBox('header_caption_pagina_before','',WTextBox::NUMERIC,'text_before'),
    new WTextBox('header_caption_pagina_after','',WTextBox::NUMERIC,'text_after'))),
    true
);
$header->add('number_of_start', 'number_of_start', new WList(array(
    new WTextBox('header_caption_nos_before','',WTextBox::NUMERIC,'text_before'),
    new WTextBox('header_caption_nos_after','',WTextBox::NUMERIC,'text_after'))),
    true
);
$header->add('number_of_end', 'number_of_end', new WList(array(
    new WTextBox('header_caption_noe_before','',WTextBox::NUMERIC,'text_before'),
    new WTextBox('header_caption_noe_after','',WTextBox::NUMERIC,'text_after'))),
    true
);
$header->add('element_count', 'element_count', new WList(array(
    new WTextBox('header_caption_count_before','',WTextBox::NUMERIC,'text_before'),
    new WTextBox('header_caption_count_after','',WTextBox::NUMERIC,'text_after'))),
    true
);
$header->render();

//Items
$items = new WPropertyEditor('itemConfig', 'items');
$items->add('title', 'title', new WList(array(
    new WConfirm('LinkTitle','link_title', true))), 
    true
);
$items->add('link', 'link', new WList(array(
    new WTextBox('item_caption_link','',WTextBox::TEXT,'caption'))), 
    false
);
$items->add('description', 'description', '', true);
$items->add('content', 'content', '', false);
$items->add('author', 'author', '', false);
$items->add('pubDate', 'pubDate', new WList(array(
    new WTextBox('item_format_pubDate','',WTextBox::TEXT,'date_format_string'))), 
    false
);
$items->add('modDate', 'modDate', new WList(array(
    new WTextBox('item_format_modDate','',WTextBox::TEXT,'date_format_string'))), 
    false
);
$items->add('tags', 'tags', new WList(array(
    new WConfirm('LinkTags','link_tags', false))), 
    false
);
$items->render();

//footer
$footer = new WPropertyEditor('footerConfig', 'footer');
$footer->add('previous_link', 'previous_link', new WList(array(
    new WTextBox('footer_caption_prev_link','',WTextBox::TEXT,'caption'))),
    true
);
$footer->add('next_link', 'next_link', new WList(array(
    new WTextBox('footer_caption_next_link','',WTextBox::TEXT,'caption'))),
    true
);
$footer->add('page_no', 'page_no', new WList(array(
    new WConfirm('footer_pagina_type','pagina_as_selection', false),
    new WTextBox('footer_caption_pagina_before','',WTextBox::NUMERIC,'text_before'),
    new WTextBox('footer_caption_pagina_after','',WTextBox::NUMERIC,'text_after'))),
    true
);
$footer->add('number_of_start', 'number_of_start', new WList(array(
    new WTextBox('footer_caption_nos_before','',WTextBox::NUMERIC,'text_before'),
    new WTextBox('footer_caption_nos_after','',WTextBox::NUMERIC,'text_after'))),
    true
);
$footer->add('number_of_end', 'number_of_end', new WList(array(
    new WTextBox('footer_caption_noe_before','',WTextBox::NUMERIC,'text_before'),
    new WTextBox('footer_caption_noe_after','',WTextBox::NUMERIC,'text_after'))),
    true
);
$footer->add('element_count', 'element_count', new WList(array(
    new WTextBox('footer_caption_count_before','',WTextBox::NUMERIC,'text_before'),
    new WTextBox('footer_caption_count_after','',WTextBox::NUMERIC,'text_after'))),
    true
);
$footer->render();


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