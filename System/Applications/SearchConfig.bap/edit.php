<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author selke@tutech.de
 * @package org.bambuscms.applications.websites
 * @since 2009-04-28
 * @version 1.0
 */
$Search = SApplication::getControllerContent();
if($Search instanceof CSearch)
{
    echo new WContentTitle($Search);
    
    $elements = array(
        'nl' => 'next_link',
        'pl' => 'previous_link',
    	'ro' => 'result_overview',
    	'sf' => 'search_form'
    );
    $options = array(
        CSearch::NEXT => 'nl', 
        CSearch::PREV => 'pl', 
        CSearch::OVERVIEW => 'ro', 
        CSearch::FORM => 'sf'
    );
    $tplSelect = sprintf("<select id=\"%%s\" onchange=\"ch(this.id, this.options[this.selectedIndex].value);\">
			<option value=\"0\">%s</option>
			<option value=\"1\">%s</option>
			<option value=\"2\">%s</option>
			<option value=\"3\">%s</option>
		</select>"
    	,SLocalization::get('hidden')
    	,SLocalization::get('above_results')
    	,SLocalization::get('below_results')
    	,SLocalization::get('above_and_below_results')
	);
	$tplInput = "<input type=\"text\" id=\"%s_%d\" />";
    
    $tbl = new WTable(WTable::HEADING_TOP|WTable::HEADING_LEFT);
    $tbl->setTitle('controls', true);
    $tbl->addRow(array('element', 'appearance', 'caption_in_above_position', 'caption_in_below_position'));
    foreach ($elements as $id => $label)
    {
        $tbl->addRow(array($label, sprintf($tplSelect,$id), sprintf($tplInput,$id,1), sprintf($tplInput,$id,2)));
    }
    echo $tbl;
    echo '<script type="text/javascript">'."\n";
    foreach($options as $opt => $id)
    {    
        echo 'set("',     
                $id, '", ',
                $Search->getMode($opt),',"',
                addslashes($Search->getCaption($opt, CSearch::ABOVE)),'","',
                addslashes($Search->getCaption($opt, CSearch::BELOW)),
            '");'."\n";
    }
    echo '</script>';
}
?>

+ Target View