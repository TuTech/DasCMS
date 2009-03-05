<?php
/************************************************
* Bambus CMS 
* Created:     28. Okt 08
* License:     GNU GPL Version 2 or later (http://www.gnu.org/copyleft/gpl.html)
* Copyright:   Lutz Selke/TuTech Innovation GmbH 
* Description: css editor interface
************************************************/
//editing allowed?
	
if(PAuthorisation::has('org.bambuscms.content.cfeed.change') && isset($Feed) && $Feed instanceof CFeed)
{
	printf('<input type="hidden" id="filename" size="30" name="filename" value="%s"/><h2>%s</h2>'
		, htmlentities($Feed->Title, ENT_QUOTES, 'UTF-8')
		, htmlentities($Feed->Title, ENT_QUOTES, 'UTF-8')
	);

    $tbl = new WTable(WTable::HEADING_TOP|WTable::HEADING_LEFT, 'settings');
    $tbl->setHeaderTranslation(true);
    $tbl->addRow(array(
        'description', 
        'value'
    ));
    $tbl->addRow(array(
        'items_per_page', 
        new WTextBox(
            'so_ItemsPerPage', 
            $Feed->option(CFeed::SETTINGS, 'ItemsPerPage'), 
            WTextBox::NUMERIC
        )
    ));
    $tbl->addRow(array(
        'max_number_of_pages', 
        new WTextBox(
            'so_MaxPages', 
            $Feed->option(CFeed::SETTINGS, 'MaxPages'), 
            WTextBox::NUMERIC
        )
    ));
    $tbl->addRow(array(
        'filter_method', 
        new WMultipleChoice(
            'so_FilterMethod',
            array(
                CFeed::ALL => 'unfiltered', 
                CFeed::MATCH_ALL => 'match_all_in_list',
                CFeed::MATCH_SOME => 'match_any_in_list',
                CFeed::MATCH_NONE => 'match_items_not_tagged_with_any_of_list'
    		),
            $Feed->option(CFeed::SETTINGS, 'FilterMethod'),
            WMultipleChoice::SELECT
        )
    ));
    $tbl->addRow(array(
        'filter', 
        new WTextBox(
            'so_Filter', 
            implode(', ',$Feed->option(CFeed::SETTINGS, 'Filter')), 
            WTextBox::MULTILINE
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
        new WMultipleChoice(
            'so_TargetView',
            $targetViews,
            $Feed->option(CFeed::SETTINGS, 'TargetView'),
            WMultipleChoice::SELECT, 
            false
        )
    ));
    $tbl->addRow(array(
        'sort_order', 
        new WMultipleChoice(
            'so_SortOrder',
            array(
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
            'so_SortBy',
            array(
                'title' => 'title', 
                'pubdate' => 'pubDate'
    		),
            $Feed->option(CFeed::SETTINGS, 'SortBy'),
            WMultipleChoice::RADIO
        )
    ));
    $tbl->addRow(array(
        'no_items_text', 
        new WTextBox(
            'icp_NoItemsFound', 
            $Feed->caption(CFeed::ITEM, 'NoItemsFound', CFeed::PREFIX), 
            WTextBox::TEXT
        )
    ));
    $tbl->render();
    
    //header 
    $header = new WPropertyEditor('headerConfig', 'header');
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
                $header->add($itemMap[$option], $itemMap[$option], new WList(array(
                    new WTextBox('hcp_Link',$Feed->caption(CFeed::HEADER, 'Link', CFeed::PREFIX),WTextBox::TEXT,'caption'))),
                    $pos !== null
                );
                break;
            case 'NextLink':
                $header->add($itemMap[$option], $itemMap[$option], new WList(array(
                    new WTextBox('hcs_Link',$Feed->caption(CFeed::HEADER, 'Link', CFeed::SUFFIX),WTextBox::TEXT,'caption'))),
                    $pos !== null
                );
                break;
            case 'Pagina':
                $header->add($itemMap[$option], $itemMap[$option], new WList(array(
                    new WConfirm('ho_PaginaType','pagina_as_selection', $Feed->option(CFeed::HEADER, 'PaginaType')),
                    new WTextBox('hcp_Pagina',$Feed->caption(CFeed::HEADER, 'Pagina', CFeed::PREFIX),WTextBox::NUMERIC,'text_before'),
                    new WTextBox('hcs_Pagina',$Feed->caption(CFeed::HEADER, 'Pagina', CFeed::SUFFIX),WTextBox::NUMERIC,'text_after'))),
                    $pos !== null
                );
                break;
            case 'NumberOfStart':
                $header->add($itemMap[$option], $itemMap[$option], new WList(array(
                    new WTextBox('hcp_NumberOfStart',$Feed->caption(CFeed::HEADER, 'NumberOfStart', CFeed::PREFIX),WTextBox::NUMERIC,'text_before'),
                    new WTextBox('hcs_NumberOfStart',$Feed->caption(CFeed::HEADER, 'NumberOfStart', CFeed::SUFFIX),WTextBox::NUMERIC,'text_after'))),
                    $pos !== null
                );
                break;
            case 'NumberOfEnd':
                $header->add($itemMap[$option], $itemMap[$option], new WList(array(
                    new WTextBox('hcp_NumberOfEnd',$Feed->caption(CFeed::HEADER, 'NumberOfEnd', CFeed::PREFIX),WTextBox::NUMERIC,'text_before'),
                    new WTextBox('hcs_NumberOfEnd',$Feed->caption(CFeed::HEADER, 'NumberOfEnd', CFeed::SUFFIX),WTextBox::NUMERIC,'text_after'))),
                    $pos !== null
                );
                break;
            case 'FoundItems':
                $header->add($itemMap[$option], $itemMap[$option], new WList(array(
                    new WTextBox('hcp_FoundItems',$Feed->caption(CFeed::HEADER, 'FoundItems', CFeed::PREFIX),WTextBox::NUMERIC,'text_before'),
                    new WTextBox('hcs_FoundItems',$Feed->caption(CFeed::HEADER, 'FoundItems', CFeed::SUFFIX),WTextBox::NUMERIC,'text_after'))),
                    $pos !== null
                );
                break;
        }
    }
    $header->render();
    
    //Items
    $items = new WPropertyEditor('itemConfig', 'items');
    $itemMap = array(
        'Content' => 'content',
        'Link' => 'link',
        'Tags' => 'tags',
        'ModDate' => 'modDate',
        'Title' => 'title',
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
                $items->add($itemMap[$option], $itemMap[$option], new WList(array(
                    new WConfirm('io_LinkTitle','link_title', $Feed->option(CFeed::ITEM, 'LinkTitle')))), 
                    $pos !== null
                );
                break;
            case 'Link':
                $items->add($itemMap[$option], $itemMap[$option], new WList(array(
                    new WTextBox('icp_Link',$Feed->caption(CFeed::ITEM, 'Link', CFeed::PREFIX),WTextBox::TEXT,'caption'))), 
                    $pos !== null
                );
                break;
            case 'Description':
            case 'Content':
            case 'Author':
                $items->add($itemMap[$option], $itemMap[$option], '', $pos !== null);
                break;
            case 'Icon':
                $piConf = new WTable(WTable::HEADING_LEFT, 'icon_config');
                $piConf->addRow(array('size', 
                    new WMultipleChoice('io_IconSize', array(
                                WIcon::EXTRA_SMALL => 'extra_small_icons', 
                                WIcon::SMALL => 'small_icons', 
                                WIcon::MEDIUM => 'medium_icons', 
                                WIcon::LARGE => 'large_icons'
                            ),
                            intval($Feed->option(CFeed::ITEM, 'IconSize'))
                            ,WMultipleChoice::RADIO
                        )
                ));
                $piConf->addRow(array('linking', new WConfirm('io_LinkIcon','link_icon', $Feed->option(CFeed::ITEM, 'LinkIcon'))));
                $items->add($itemMap[$option], $itemMap[$option], $piConf, $pos !== null);
                break;
            case 'PreviewImage':
                $piConf = new WTable(WTable::HEADING_LEFT, 'preview_scaling_config');
                $piConf->addRow(array('width', new WTextBox('io_PreviewImageWidth', $Feed->option(CFeed::ITEM, 'PreviewImageWidth'), WTextBox::NUMERIC)));
                $piConf->addRow(array('height', new WTextBox('io_PreviewImageHeight', $Feed->option(CFeed::ITEM, 'PreviewImageHeight'), WTextBox::NUMERIC)));
                $piConf->addRow(array('background_color_hex', new WTextBox('io_PreviewImageBgColor', $Feed->option(CFeed::ITEM, 'PreviewImageBgColor'), WTextBox::TEXT)));
                $piConf->addRow(array('scale_method', new WMultipleChoice('io_PreviewImageMode', array(
                                '0c' => 'scale_aspect_to_fit_in_boundaries', 
                                '1c' => 'scale_aspect_and_crop', 
                                '1f' => 'scale_aspect_and_fill_background', 
                                '1s' => 'scale_by_stretch'
                            ),
                            $Feed->option(CFeed::ITEM, 'PreviewImageMode')
                            ,WMultipleChoice::SELECT
                        )));
                $piConf->addRow(array('linking', new WConfirm('io_LinkPreviewImage','link_preview_image', $Feed->option(CFeed::ITEM, 'LinkPreviewImage'))));
                $items->add($itemMap[$option], $itemMap[$option], $piConf, $pos !== null);
                break;
            case 'PubDate':
                $items->add($itemMap[$option], $itemMap[$option], new WList(array(
                    new WTextBox('io_PubDateFormat',$Feed->option(CFeed::ITEM, 'PubDateFormat'),WTextBox::TEXT,'date_format_string'))), 
                    $pos !== null
                );
                break;
            case 'ModDate':
                $items->add($itemMap[$option], $itemMap[$option], new WList(array(
                    new WTextBox('io_ModDateFormat',$Feed->option(CFeed::ITEM, 'ModDateFormat'),WTextBox::TEXT,'date_format_string'))), 
                    $pos !== null
                );
                break;
            case 'Tags':
                $items->add($itemMap[$option], $itemMap[$option], new WList(array(
                    new WConfirm('io_LinkTags','link_tags', $Feed->option(CFeed::ITEM, 'LinkTags')))), 
                    $pos !== null
                );
                break;
        }
    }
    $items->render();
    
    //footer
    $footer = new WPropertyEditor('footerConfig', 'footer');
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
                $footer->add($itemMap[$option], $itemMap[$option], new WList(array(
                    new WTextBox('fcp_Link',$Feed->caption(CFeed::FOOTER, 'Link', CFeed::PREFIX),WTextBox::TEXT,'caption'))),
                    $pos !== null
                );
                break;
            case 'NextLink':
                $footer->add($itemMap[$option], $itemMap[$option], new WList(array(
                    new WTextBox('fcs_Link',$Feed->caption(CFeed::FOOTER, 'Link', CFeed::SUFFIX),WTextBox::TEXT,'caption'))),
                    $pos !== null
                );
                break;
            case 'Pagina':
                $footer->add($itemMap[$option], $itemMap[$option], new WList(array(
                    new WConfirm('fo_PaginaType','pagina_as_selection', $Feed->option(CFeed::FOOTER, 'PaginaType')),
                    new WTextBox('fcp_Pagina',$Feed->caption(CFeed::FOOTER, 'Pagina', CFeed::PREFIX),WTextBox::NUMERIC,'text_before'),
                    new WTextBox('fcs_Pagina',$Feed->caption(CFeed::FOOTER, 'Pagina', CFeed::SUFFIX),WTextBox::NUMERIC,'text_after'))),
                    $pos !== null
                );
                break;
            case 'NumberOfStart':
                $footer->add($itemMap[$option], $itemMap[$option], new WList(array(
                    new WTextBox('fcp_NumberOfStart',$Feed->caption(CFeed::FOOTER, 'NumberOfStart', CFeed::PREFIX),WTextBox::NUMERIC,'text_before'),
                    new WTextBox('fcs_NumberOfStart',$Feed->caption(CFeed::FOOTER, 'NumberOfStart', CFeed::SUFFIX),WTextBox::NUMERIC,'text_after'))),
                    $pos !== null
                );
                break;
            case 'NumberOfEnd':
                $footer->add($itemMap[$option], $itemMap[$option], new WList(array(
                    new WTextBox('fcp_NumberOfEnd',$Feed->caption(CFeed::FOOTER, 'NumberOfEnd', CFeed::PREFIX),WTextBox::NUMERIC,'text_before'),
                    new WTextBox('fcs_NumberOfEnd',$Feed->caption(CFeed::FOOTER, 'NumberOfEnd', CFeed::SUFFIX),WTextBox::NUMERIC,'text_after'))),
                    $pos !== null
                );
                break;
            case 'FoundItems':
                $footer->add($itemMap[$option], $itemMap[$option], new WList(array(
                    new WTextBox('fcp_FoundItems',$Feed->caption(CFeed::FOOTER, 'FoundItems', CFeed::PREFIX),WTextBox::NUMERIC,'text_before'),
                    new WTextBox('fcs_FoundItems',$Feed->caption(CFeed::FOOTER, 'FoundItems', CFeed::SUFFIX),WTextBox::NUMERIC,'text_after'))),
                    $pos !== null
                );
                break;
        }
    }
    $footer->render();
    echo LGui::endForm();
}
?>