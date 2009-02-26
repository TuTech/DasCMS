<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH 
 * @package org.bambuscms.applications.configuration
 * @since 2006-10-16
 * @version 1.0
 * @author selke@tutech.de
 */
if(isset($panel) && $panel->hasWidgets())
{
    echo '<div id="objectInspectorActiveFullBox">';
}
if(PAuthorisation::has('org.bambuscms.configuration.set'))
{
	echo LGui::beginForm(array(), 'documentform');
}
printf('<h2>%s</h2>', SLocalization::get('configuration'));
$values = array(
	"website" => array(
        "pagetitle"             => array("sitename",        "fullinput"),
        "webmaster_email"       => array("webmaster",       "fullinput"),
        "copyright"             => array("copyright",       "fullinput"),
        "template_for_page_rendering"=> array("generator_content", '::CTemplate'),
        "meta_keywords"         => array("meta_keywords",   "fullinput"),
        "meta_description"      => array("meta_description","fullinput")
    ),
	"system" => array(
        "date_format"           => array("dateformat",      "fullinput"),
        "logout_on_exit"        => array("logout_on_exit",  "checkbox"),
        "confirm_for_exit"      => array("confirm_for_exit","checkbox"),
        "log_page_changes"      => array("logChanges",      "checkbox"),
        "timezone"              => array("timezone",      "tz"),
        "locale"                => array("locale",      "ISO639-2"),
        "use_wysiwyg"			=> array("use_wysiwyg",      "checkbox"),
	),
	"database_settings" => array(
        "server"                => array("db_server",       "fullinput"),
        "user"                  => array("db_user",         "fullinput"),
        "password"              => array("db_password",     "password"),
        "database_name"         => array("db_name",         "fullinput"),
        "database_table_prefix" => array("db_table_prefix", "fullinput")
	)
);
$fullinput = "\n\t\t\t<input class=\"fullinput\" type=\"text\" size=\"40\" name=\"%s\" id=\"%s\" value=\"%s\" />\n\t\t";
$password  = "\n\t\t\t<input class=\"fullinput\" type=\"password\" size=\"40\" name=\"%s\" id=\"%s\" value=\"%s\" />".
             "\n\t\t\t<br />\n\t\t\t<input type=\"checkbox\" name=\"chdbpasswd\" />%s\n\t\t";
$checkbox  = "\n\t\t\t<input type=\"checkbox\" name=\"%s\" id=\"%s\" %s/>\n\t\t";
$labeltag  = "\n\t\t\t<label title=\"{%s}\" for=\"%s\">%s</label>\n\t\t";
foreach($values as $title => $settings)
{
    $tbl = new WTable(WTable::HEADING_TOP|WTable::HEADING_LEFT, $title);
    $tbl->addRow(array('description', 'value'));
	foreach($settings as $name => $options)
	{
		list($key, $type) = $options;
		switch ($type) 
		{
            case 'fullinput':
                $input = sprintf(
                    $fullinput
                    ,$key
                    ,$key
                    ,htmlentities(LConfiguration::get($key))
                );
                break;
            
            case 'checkbox':
                $input = sprintf(
                    $checkbox
                    ,$key
                    ,$key
                    ,(LConfiguration::get($key) != '1') ? '' : ' checked="checked"'
                );
                break;
            case 'password':
                $input = sprintf(
                    $password
                    ,$key                    
                    ,$key
                    ,(trim(LConfiguration::get($key)) != '') ? '#######' : '' 
                    ,SLocalization::get('change_'.$key)
                );
                break;
            case 'ISO639-2':
                $fp = fopen(SPath::SYSTEM_RESOURCES.'ISO-639-2_utf-8.txt', 'r');
                $input = '<select name ="'.$key.'">';
                while($row = fgetcsv($fp,1024,'|'))
                {
                    $input .= sprintf('<option value="%s"%s>%s (%s)</option>%s',$row[0] , (LConfiguration::get($key) == $row[0] ? ' selected="selected"' : '') ,$row[3], $row[0] ,"\n");
                }
                $input .= '</select>';
                fclose($fp);
                break;            
            case 'tz':
                $fp = fopen(SPath::SYSTEM_RESOURCES.'timezones.txt', 'r');
                $input = '<select name ="'.$key.'">';
                while($row = fgets($fp,255))
                {
                    $row = trim($row);
                    $input .= sprintf('<option%s>%s</option>%s',(LConfiguration::get($key) == $row ? ' selected="selected"' : ''),$row ,"\n");
                }
                $input .= '</select>';
                break;
            default:
                if(substr($type, 0,2) == '::')
                {
                    $SCI = SContentIndex::alloc()->init();
                    $current = SAlias::getCurrent(LConfiguration::get($key));
                    $class = substr($type,2);
                    $options = $SCI->getIndex($class, false);
                    $input = sprintf("<select name=\"%s\">\n", $key);
                    foreach ($options as $alias => $data) 
                    {
                        $sel = ($alias == $current) ? ' selected="selected"' : '';
                    	$input .= sprintf("\t<option value=\"%s\"%s>%s (%s)</option>\n", $alias, $sel,  htmlentities($data[0], ENT_QUOTES, 'UTF-8'), $alias);
                    }
                    $input .= "</select>\n";
                }
		}
		$label = sprintf($labeltag, $key, $key, SLocalization::get($name));
		$tbl->addRow(array($label, $input));
	}
    $tbl->render();
}
if(PAuthorisation::has('org.bambuscms.configuration.set'))
{
	echo LGui::hiddenInput('writeconfig','1');
	echo LGui::endForm();
}
if(isset($panel) && $panel->hasWidgets())
{
    echo '</div>';
}
?>