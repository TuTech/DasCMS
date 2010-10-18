<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH 
 * @author selke@tutech.de
 * @package org.bambuscms.applications.feededitor
 * @since 2008-10-24
 * @version 1.0
 */
$Feed = SApplication::getControllerContent();
if(PAuthorisation::has('org.bambuscms.content.cfeed.change') && $Feed != null)
{
	echo new View_UIElement_ContentTitle($Feed);

    $tbl = new View_UIElement_Table(View_UIElement_Table::HEADING_TOP|View_UIElement_Table::HEADING_LEFT, 'settings');
    $tbl->setHeaderTranslation(true);
    $tbl->addRow(array(
        'description', 
        'value'
    ));
    $tbl->addRow(array(
        'items_per_page', 
        new View_UIElement_TextBox(
            'so_ItemsPerPage', 
            $Feed->option(CFeed::SETTINGS, 'ItemsPerPage'), 
            View_UIElement_TextBox::NUMERIC
        )
    ));
    $tbl->addRow(array(
        'max_number_of_pages', 
        new View_UIElement_TextBox(
            'so_MaxPages', 
            $Feed->option(CFeed::SETTINGS, 'MaxPages'), 
            View_UIElement_TextBox::NUMERIC
        )
    ));
    $tbl->addRow(array(
        'filter_method', 
        new View_UIElement_MultipleChoice(
            'so_FilterMethod',
            array(
                CFeed::ALL => 'unfiltered', 
                CFeed::MATCH_ALL => 'match_all_in_list',
                CFeed::MATCH_SOME => 'match_any_in_list',
                CFeed::MATCH_NONE => 'match_items_not_tagged_with_any_of_list'
    		),
            $Feed->option(CFeed::SETTINGS, 'FilterMethod'),
            View_UIElement_MultipleChoice::SELECT
        )
    ));
    $tbl->addRow(array(
        'filter', 
        new View_UIElement_TextBox(
            'so_Filter', 
            implode(', ',$Feed->option(CFeed::SETTINGS, 'Filter')), 
            View_UIElement_TextBox::MULTILINE
        )
    ));
    $views = VSpore::activeSpores();
    $targetViews = array('' => SLocalization::get('do_not_link'));
    foreach ($views as $view) 
    {
    	$targetViews[$view] = $view;
    }
    $tbl->addRow(array(
        'target_view', 
        new View_UIElement_MultipleChoice(
            'so_TargetView',
            $targetViews,
            $Feed->option(CFeed::SETTINGS, 'TargetView'),
            View_UIElement_MultipleChoice::SELECT, 
            false
        )
    ));
    $tbl->addRow(array(
        'target_frame', 
        new View_UIElement_TextBox(
            'so_TargetFrame', 
            $Feed->option(CFeed::SETTINGS, 'TargetFrame'),
            View_UIElement_TextBox::TEXT
        )
    ));

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
            $Feed->getChildContentFormatter(),
            View_UIElement_MultipleChoice::SELECT,
            false
        )
    ));
    
    $tbl->addRow(array(
        'sort_order', 
        new View_UIElement_MultipleChoice(
            'so_SortOrder',
            array(
                'ASC' => 'ascending', 
                'DESC' => 'descending'
    		),
            ($Feed->option(CFeed::SETTINGS, 'SortOrder') ? 'DESC' : 'ASC'),
            View_UIElement_MultipleChoice::RADIO
        )
    ));
    $tbl->addRow(array(
        'sort_by', 
        new View_UIElement_MultipleChoice(
            'so_SortBy',
            array(
                'title' => 'title', 
                'pubdate' => 'pubDate'
    		),
            $Feed->option(CFeed::SETTINGS, 'SortBy'),
            View_UIElement_MultipleChoice::RADIO
        )
    ));
    $tbl->addRow(array(
        'no_items_text', 
        new View_UIElement_TextBox(
            'icp_NoItemsFound', 
            $Feed->caption(CFeed::ITEM, 'NoItemsFound', CFeed::PREFIX), 
            View_UIElement_TextBox::TEXT
        )
    ));
    $tbl->render();
    
    //header 
    $header = new View_UIElement_PropertyEditor('headerConfig', 'header');
    $itemMap = array(
        'NumberOfStart' => 'number_of_start',
        'NumberOfEnd' => 'number_of_end',
        'FoundItems' => 'element_count',
        'PrevLink' => 'previous_link',
        'Pagina' => 'page_no',
        'NextLink' => 'next_link'
    );
    foreach ($Feed->order(CFeed::HEADER) as $option => $pos) 
    {
        switch($option)
        {
            case 'PrevLink':
                $header->add($itemMap[$option], $itemMap[$option], new View_UIElement_List(array(
                    new View_UIElement_TextBox('hcp_Link',$Feed->caption(CFeed::HEADER, 'Link', CFeed::PREFIX),View_UIElement_TextBox::TEXT,'caption'))),
                    $pos !== null
                );
                break;
            case 'NextLink':
                $header->add($itemMap[$option], $itemMap[$option], new View_UIElement_List(array(
                    new View_UIElement_TextBox('hcs_Link',$Feed->caption(CFeed::HEADER, 'Link', CFeed::SUFFIX),View_UIElement_TextBox::TEXT,'caption'))),
                    $pos !== null
                );
                break;
            case 'Pagina':
                $header->add($itemMap[$option], $itemMap[$option], new View_UIElement_List(array(
                    new View_UIElement_Confirm('ho_PaginaType','pagina_as_selection', $Feed->option(CFeed::HEADER, 'PaginaType')),
                    new View_UIElement_TextBox('hcp_Pagina',$Feed->caption(CFeed::HEADER, 'Pagina', CFeed::PREFIX),View_UIElement_TextBox::NUMERIC,'text_before'),
                    new View_UIElement_TextBox('hcs_Pagina',$Feed->caption(CFeed::HEADER, 'Pagina', CFeed::SUFFIX),View_UIElement_TextBox::NUMERIC,'text_after'))),
                    $pos !== null
                );
                break;
            case 'NumberOfStart':
                $header->add($itemMap[$option], $itemMap[$option], new View_UIElement_List(array(
                    new View_UIElement_TextBox('hcp_NumberOfStart',$Feed->caption(CFeed::HEADER, 'NumberOfStart', CFeed::PREFIX),View_UIElement_TextBox::NUMERIC,'text_before'),
                    new View_UIElement_TextBox('hcs_NumberOfStart',$Feed->caption(CFeed::HEADER, 'NumberOfStart', CFeed::SUFFIX),View_UIElement_TextBox::NUMERIC,'text_after'))),
                    $pos !== null
                );
                break;
            case 'NumberOfEnd':
                $header->add($itemMap[$option], $itemMap[$option], new View_UIElement_List(array(
                    new View_UIElement_TextBox('hcp_NumberOfEnd',$Feed->caption(CFeed::HEADER, 'NumberOfEnd', CFeed::PREFIX),View_UIElement_TextBox::NUMERIC,'text_before'),
                    new View_UIElement_TextBox('hcs_NumberOfEnd',$Feed->caption(CFeed::HEADER, 'NumberOfEnd', CFeed::SUFFIX),View_UIElement_TextBox::NUMERIC,'text_after'))),
                    $pos !== null
                );
                break;
            case 'FoundItems':
                $header->add($itemMap[$option], $itemMap[$option], new View_UIElement_List(array(
                    new View_UIElement_TextBox('hcp_FoundItems',$Feed->caption(CFeed::HEADER, 'FoundItems', CFeed::PREFIX),View_UIElement_TextBox::NUMERIC,'text_before'),
                    new View_UIElement_TextBox('hcs_FoundItems',$Feed->caption(CFeed::HEADER, 'FoundItems', CFeed::SUFFIX),View_UIElement_TextBox::NUMERIC,'text_after'))),
                    $pos !== null
                );
                break;
        }
    }
    $header->render();
    
    //Items
    $items = new View_UIElement_PropertyEditor('itemConfig', 'items');
    $itemMap = array(
        'Content' => 'content',
        'Link' => 'link',
        'Tags' => 'tags',
        'ModDate' => 'modDate',
        'Title' => 'title',
        'SubTitle' => 'subtitle',
    	'Description' => 'description',
        'Author' => 'author',
        'PubDate' => 'pubDate',
        'Icon' => 'icon',
        'PreviewImage' => 'previewImage'
    );
    foreach ($Feed->order(CFeed::ITEM) as $option => $pos) 
    {
        switch($option)
        {
            case 'Title':
                $items->add($itemMap[$option], $itemMap[$option], new View_UIElement_List(array(
                    new View_UIElement_Confirm('io_LinkTitle','link_title', $Feed->option(CFeed::ITEM, 'LinkTitle')))), 
                    $pos !== null
                );
                break;
            case 'Link':
                $items->add($itemMap[$option], $itemMap[$option], new View_UIElement_List(array(
                    new View_UIElement_TextBox('icp_Link',$Feed->caption(CFeed::ITEM, 'Link', CFeed::PREFIX),View_UIElement_TextBox::TEXT,'caption'))), 
                    $pos !== null
                );
                break;
            case 'SubTitle':
            case 'Description':
            case 'Content':
            case 'Author':
                $items->add($itemMap[$option], $itemMap[$option], '', $pos !== null);
                break;
            case 'Icon':
                $piConf = new View_UIElement_Table(View_UIElement_Table::HEADING_LEFT, 'icon_config');
                $piConf->addRow(array('size', 
                    new View_UIElement_MultipleChoice('io_IconSize', array(
                                View_UIElement_Icon::EXTRA_SMALL => 'extra_small_icons', 
                                View_UIElement_Icon::SMALL => 'small_icons', 
                                View_UIElement_Icon::MEDIUM => 'medium_icons', 
                                View_UIElement_Icon::LARGE => 'large_icons'
                            ),
                            intval($Feed->option(CFeed::ITEM, 'IconSize'))
                            ,View_UIElement_MultipleChoice::RADIO
                        )
                ));
                $piConf->addRow(array('linking', new View_UIElement_Confirm('io_LinkIcon','link_icon', $Feed->option(CFeed::ITEM, 'LinkIcon'))));
                $items->add($itemMap[$option], $itemMap[$option], $piConf, $pos !== null);
                break;
            case 'PreviewImage':
                $piConf = new View_UIElement_Table(View_UIElement_Table::HEADING_LEFT, 'preview_scaling_config');
                $piConf->addRow(array('width', new View_UIElement_TextBox('io_PreviewImageWidth', $Feed->option(CFeed::ITEM, 'PreviewImageWidth'), View_UIElement_TextBox::NUMERIC)));
                $piConf->addRow(array('height', new View_UIElement_TextBox('io_PreviewImageHeight', $Feed->option(CFeed::ITEM, 'PreviewImageHeight'), View_UIElement_TextBox::NUMERIC)));
                $piConf->addRow(array('background_color_hex', new View_UIElement_TextBox('io_PreviewImageBgColor', $Feed->option(CFeed::ITEM, 'PreviewImageBgColor'), View_UIElement_TextBox::TEXT)));
                $piConf->addRow(array('scale_method', new View_UIElement_MultipleChoice('io_PreviewImageMode', array(
                                '0c' => 'scale_aspect_to_fit_in_boundaries', 
                                '1c' => 'scale_aspect_and_crop', 
                                '1f' => 'scale_aspect_and_fill_background', 
                                '1s' => 'scale_by_stretch'
                            ),
                            $Feed->option(CFeed::ITEM, 'PreviewImageMode')
                            ,View_UIElement_MultipleChoice::SELECT
                        )));
                $piConf->addRow(array('linking', new View_UIElement_Confirm('io_LinkPreviewImage','link_preview_image', $Feed->option(CFeed::ITEM, 'LinkPreviewImage'))));
                $items->add($itemMap[$option], $itemMap[$option], $piConf, $pos !== null);
                break;
            case 'PubDate':
                $items->add($itemMap[$option], $itemMap[$option], new View_UIElement_List(array(
                    new View_UIElement_TextBox('io_PubDateFormat',$Feed->option(CFeed::ITEM, 'PubDateFormat'),View_UIElement_TextBox::TEXT,'date_format_string'))), 
                    $pos !== null
                );
                break;
            case 'ModDate':
                $items->add($itemMap[$option], $itemMap[$option], new View_UIElement_List(array(
                    new View_UIElement_TextBox('io_ModDateFormat',$Feed->option(CFeed::ITEM, 'ModDateFormat'),View_UIElement_TextBox::TEXT,'date_format_string'))), 
                    $pos !== null
                );
                break;
            case 'Tags':
                $items->add($itemMap[$option], $itemMap[$option], new View_UIElement_List(array(
                    new View_UIElement_Confirm('io_LinkTags','link_tags', $Feed->option(CFeed::ITEM, 'LinkTags')))), 
                    $pos !== null
                );
                break;
        }
    }
    $items->render();
    
    //footer
    $footer = new View_UIElement_PropertyEditor('footerConfig', 'footer');
    $itemMap = array(
        'NumberOfStart' => 'number_of_start',
        'NumberOfEnd' => 'number_of_end',
        'FoundItems' => 'element_count',
        'PrevLink' => 'previous_link',
        'Pagina' => 'page_no',
        'NextLink' => 'next_link'
    );
    foreach ($Feed->order(CFeed::FOOTER) as $option => $pos) 
    {
        switch($option)
        {
            case 'PrevLink':
                $footer->add($itemMap[$option], $itemMap[$option], new View_UIElement_List(array(
                    new View_UIElement_TextBox('fcp_Link',$Feed->caption(CFeed::FOOTER, 'Link', CFeed::PREFIX),View_UIElement_TextBox::TEXT,'caption'))),
                    $pos !== null
                );
                break;
            case 'NextLink':
                $footer->add($itemMap[$option], $itemMap[$option], new View_UIElement_List(array(
                    new View_UIElement_TextBox('fcs_Link',$Feed->caption(CFeed::FOOTER, 'Link', CFeed::SUFFIX),View_UIElement_TextBox::TEXT,'caption'))),
                    $pos !== null
                );
                break;
            case 'Pagina':
                $footer->add($itemMap[$option], $itemMap[$option], new View_UIElement_List(array(
                    new View_UIElement_Confirm('fo_PaginaType','pagina_as_selection', $Feed->option(CFeed::FOOTER, 'PaginaType')),
                    new View_UIElement_TextBox('fcp_Pagina',$Feed->caption(CFeed::FOOTER, 'Pagina', CFeed::PREFIX),View_UIElement_TextBox::NUMERIC,'text_before'),
                    new View_UIElement_TextBox('fcs_Pagina',$Feed->caption(CFeed::FOOTER, 'Pagina', CFeed::SUFFIX),View_UIElement_TextBox::NUMERIC,'text_after'))),
                    $pos !== null
                );
                break;
            case 'NumberOfStart':
                $footer->add($itemMap[$option], $itemMap[$option], new View_UIElement_List(array(
                    new View_UIElement_TextBox('fcp_NumberOfStart',$Feed->caption(CFeed::FOOTER, 'NumberOfStart', CFeed::PREFIX),View_UIElement_TextBox::NUMERIC,'text_before'),
                    new View_UIElement_TextBox('fcs_NumberOfStart',$Feed->caption(CFeed::FOOTER, 'NumberOfStart', CFeed::SUFFIX),View_UIElement_TextBox::NUMERIC,'text_after'))),
                    $pos !== null
                );
                break;
            case 'NumberOfEnd':
                $footer->add($itemMap[$option], $itemMap[$option], new View_UIElement_List(array(
                    new View_UIElement_TextBox('fcp_NumberOfEnd',$Feed->caption(CFeed::FOOTER, 'NumberOfEnd', CFeed::PREFIX),View_UIElement_TextBox::NUMERIC,'text_before'),
                    new View_UIElement_TextBox('fcs_NumberOfEnd',$Feed->caption(CFeed::FOOTER, 'NumberOfEnd', CFeed::SUFFIX),View_UIElement_TextBox::NUMERIC,'text_after'))),
                    $pos !== null
                );
                break;
            case 'FoundItems':
                $footer->add($itemMap[$option], $itemMap[$option], new View_UIElement_List(array(
                    new View_UIElement_TextBox('fcp_FoundItems',$Feed->caption(CFeed::FOOTER, 'FoundItems', CFeed::PREFIX),View_UIElement_TextBox::NUMERIC,'text_before'),
                    new View_UIElement_TextBox('fcs_FoundItems',$Feed->caption(CFeed::FOOTER, 'FoundItems', CFeed::SUFFIX),View_UIElement_TextBox::NUMERIC,'text_after'))),
                    $pos !== null
                );
                break;
        }
    }
    $footer->render();
    
}
?>