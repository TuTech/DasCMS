<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH 
 * @author selke@tutech.de
 * @package org.bambuscms.applications.views
 * @since 2007-11-28
 * @version 1.0
 */
if(PAuthorisation::has('org.bambuscms.resolver.vspore.change'))
{
	printf(
		'<input type="hidden" name="posted" value="1" />'
	);
}
$cell1TPL = 
	'<input type="text" name="%s" value="" onkeyup="org.bambuscms.validators.spore(this);" '.
		'onblur="org.bambuscms.validators.spore(this);" onchange="org.bambuscms.validators.spore(this);"/>';
$cell2TPL = 
	'<input type="checkbox" name="%s" %s/>';
$cell3and4TPL = 
    new View_UIElement_Template(
    	'<a class="right" href="javascript:clearOpt(\'{action}_{subject}\');">'.
    		'<img src="System/ClientData/Icons/16x16/actions/delete.png" alt="remove" title="remove" />'.
    	'</a>'.
        '<input readonly="readonly" type="hidden" onfocus="lastFocus = \'{action}_{subject}\';" id="{action}_{subject}" name="{action}_{subject}" value="{id}" />'.
        '<input readonly="readonly" type="text"   onfocus="lastFocus = \'{action}_{subject}\';" id="{action}_{subject}_t" value="{title}" />',
        View_UIElement_Template::STRING
    );

$tbl = new View_UIElement_Table(View_UIElement_Table::HEADING_TOP);
$tbl->setTitle('create_new_view', true);
$tbl->setHeaderTranslation(true);
$tbl->setCSSId('newSporeTable');
$tbl->addRow(array('access_var', 'active', 'default_content', 'error_content'));
$tbl->addRow(array(
    sprintf($cell1TPL, "new_spore"),
    sprintf($cell2TPL, "new_actv", ""),
    $cell3and4TPL->renderString(array(
        'action' => 'new',
        'subject' => 'init',
        'id' => '',
        'title' => ''
    )),
    $cell3and4TPL->renderString(array(
        'action' => 'new',
        'subject' => 'err',
        'id' => '',
        'title' => ''
    ))
));
echo $tbl;

$sporeData = Controller_View_Content::getSpores();
$spores = array_keys($sporeData);

if(count($spores) > 0)
{
    $cell1TPL = 
        new View_UIElement_Template(
        	'<a class="right" href="javascript:toggleSporeRemove(\'{spore}\');">'.
        		'<img id="spore_{spore}_rm" src="System/ClientData/Icons/16x16/actions/delete.png" alt="set remove flag" title="set remove flag" />'.
        		'<img id="spore_{spore}_norm" style="display:none;" src="System/ClientData/Icons/16x16/actions/refresh.png" alt="unset remove flag" title="unset remove flag" />'.
        	'</a>'.
        	'<span id="spore_{spore}_t">{spore}</span>'.
        	'<input type="hidden" id="spore_{spore}" name="spore_{spore}"value="" />',
            View_UIElement_Template::STRING
        );
    
    $tbl = new View_UIElement_Table(View_UIElement_Table::HEADING_TOP);
    $tbl->setTitle('current_views', true);
    $tbl->setHeaderTranslation(true);
    $tbl->setCSSId('spores');
    $tbl->addRow(array('access_var', 'active', 'default_content', 'error_content'));
	foreach ($sporeData as $spore => $data) 
	{
		$initCTitle = '';
		$initCID = '';
		if(!empty($data[Controller_View_Content::INIT_CONTENT]))
		{
			$alias = $data[Controller_View_Content::INIT_CONTENT];
			$content = Controller_Content::getInstance()->tryOpenContent($alias);
			$initCTitle = $content->Title;
			$initCID = $data[Controller_View_Content::INIT_CONTENT];
		}
		
		$errCTitle = '';
		$errCID = '';
		if(!empty($data[Controller_View_Content::ERROR_CONTENT]))
		{
			$alias = $data[Controller_View_Content::ERROR_CONTENT];
			$content = Controller_Content::getInstance()->tryOpenContent($alias);
			$errCTitle = $content->Title;
			$errCID = $data[Controller_View_Content::ERROR_CONTENT];
		}
		
		$check = ($data[Controller_View_Content::ACTIVE]) ? ' checked="checked"' : '';
		$outSpore = htmlentities($spore, ENT_QUOTES, CHARSET);
		
		$tbl->addRow(array(
            $cell1TPL->renderString(array('spore' => $outSpore)),
            sprintf($cell2TPL, "actv_".$outSpore, $check),
            $cell3and4TPL->renderString(array(
                'action' => 'init',
                'subject' => $outSpore,
                'id' => $initCID,
                'title' => $initCTitle.' ('.$initCID.')'
            )),
            $cell3and4TPL->renderString(array(
                'action' => 'err',
                'subject' => $outSpore,
                'id' => $errCID,
                'title' => $errCTitle.' ('.$errCID.')'
            ))
        ));
	}
	echo $tbl;//'</table>';
}
else
{
	echo '<h3>', SLocalization::get('please_add_at_least_one_-_you_need_them_for_viewing_any_content,_really'),'</h3>';
}
	?>
