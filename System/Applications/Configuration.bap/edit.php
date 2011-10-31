<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH 
 * @author selke@tutech.de
 * @package org.bambuscms.applications.configuration
 * @since 2006-10-16
 * @version 1.0
 */
printf('<h2>%s</h2>', SLocalization::get('configuration'));
$fullinput = "\n\t\t\t<input class=\"fullinput\" type=\"%s\" size=\"40\" name=\"%s\" id=\"%s\" value=\"%s\" />\n\t\t";
$infotext = "\n\t\t\t<span class=\"information-text\">%s</span>\n\t\t";
/**
 * @var Controller_Application_Configuration
 */
$ctrl = SApplication::appController();
//$ctrl = new Controller_Application_Configuration();
$settings = $ctrl->getSettings();
$sorted = array();
foreach ($settings as $section => $data)
{
    $sorted[$section] = strtolower(SLocalization::get($section));
}
asort($sorted, SORT_STRING);
foreach ($sorted as $section => $loc)
{
    $data = $settings[$section];
    if(count($data))
    {
        $tbl = new View_UIElement_Table(View_UIElement_Table::HEADING_LEFT);
        $tbl->setHeaderTranslation(true);
        $tbl->setTitle($section, true);
        foreach ($data as $key => $fieldconfig)
        {
            list($langKey, $value, $type, $options, $label) = $fieldconfig;
            $str = '';
            switch ($type)
            {
                case Settings::TYPE_INFORMATION:
                    $str = sprintf($infotext, String::htmlEncode($value));
                    break;
                case Settings::TYPE_TEXT:
                    $str = sprintf($fullinput, 'text', $key, $key, String::htmlEncode($value));
                    break;
                case Settings::TYPE_PASSWORD:
                    $str = sprintf($fullinput, 'password', $key, $key, String::htmlEncode($value));
                    break;
                case Settings::TYPE_CHECKBOX:
                    $str = sprintf(
                    	"<input type=\"checkbox\" name=\"%s\" id=\"%s\"%s /><input type=\"hidden\" name=\"_%s\" value=\"1\" />"
                        ,$key
                        ,$key
                        ,empty($value) ? '' : ' checked="checked"'
                        ,$key
                    );
                    break;
                case Settings::TYPE_SELECT:
                    $str = sprintf("<select name=\"%s\" id=\"%s\">\n", $key, $key);
                    if(is_array($options))
                    {
                        foreach ($options as $title => $opt)
                        {
                            $str .= sprintf("\t<option value=\"%s\"%s>%s</option>\n" 
                                ,String::htmlEncode($opt)
                                ,$opt == $value ? ' selected="selected"' : ''
                                ,String::htmlEncode((is_int($title) ? $opt : $title))
                            );
                        }
                    }
                    $str .= sprintf("</select>\n");
                    break;
            }
            
            $tbl->addRow(array($label, $str));
        }
        $tbl->render();
    }
}
?><br />&nbsp;
<script type="text/javascript">
$(function(){
	$('#document').addClass("mode-config");
});
</script>