<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH 
 * @author selke@tutech.de
 * @package org.bambuscms.applications.configuration
 * @since 2006-10-16
 * @version 1.0
 */
printf('<h2>%s</h2>', SLocalization::get('configuration'));
$values = array(
	"website" => array(
        "pagetitle"             => array("sitename",        "fullinput"),
        "webmaster_email"       => array("webmaster",       "fullinput"),
        "copyright"             => array("copyright",       "fullinput"),
        "template_for_page_rendering"=> array("generator_content", '::CTemplate'),
        'login_template'        => array("login_template", ':::CTemplate'),
		"meta_keywords"         => array("meta_keywords",   "fullinput"),
        "meta_description"      => array("meta_description","fullinput"),
		'preview_image_quality' => array("preview_image_quality","image_quality"),
		'wellformed_urls'       => array("wellformed_urls", "checkbox"),
    ),
	"system" => array(
        "date_format"           => array("dateformat",      "fullinput"),
        "logout_on_exit"        => array("logout_on_exit",  "checkbox"),
        "confirm_for_exit"      => array("confirm_for_exit","checkbox"),
        "log_page_changes"      => array("logChanges",      "checkbox"),
        "timezone"              => array("timezone",      "tz"),
        "locale"                => array("locale",      "ISO639-2"),
        "use_wysiwyg"			=> array("use_wysiwyg",      "checkbox"),
    	'mail_webmaster_on_error'=> array("mail_webmaster_on_error","checkbox"),
	),
	"database_settings" => array(
        "server"                => array("db_server",       "fullinput"),
        "user"                  => array("db_user",         "fullinput"),
        "password"              => array("db_password",     "password"),
        "database_name"         => array("db_name",         "fullinput"),
       // "database_table_prefix" => array("db_table_prefix", "fullinput")
	)
);
$fullinput = "\n\t\t\t<input class=\"fullinput\" type=\"text\" size=\"40\" name=\"%s\" id=\"%s\" value=\"%s\" />\n\t\t";
$password  = "\n\t\t\t<input class=\"fullinput\" type=\"password\" size=\"40\" name=\"%s\" id=\"%s\" value=\"%s\" />".
             "\n\t\t\t<br />\n\t\t\t<input type=\"checkbox\" name=\"chdbpasswd\" />%s\n\t\t";
$checkbox  = "\n\t\t\t<input type=\"checkbox\" name=\"%s\" id=\"%s\" %s/>\n\t\t";
$labeltag  = "\n\t\t\t<label title=\"%s: &quot;%s&quot;\" for=\"%s\">%s</label>\n\t\t";
foreach($values as $title => $settings)
{
    $tbl = new WTable(WTable::HEADING_TOP|WTable::HEADING_LEFT, $title);
    $tbl->setHeaderTranslation(false);
    $tbl->addRow(array(SLocalization::get('description'), SLocalization::get('value')));
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
            case 'image_quality':
                $input = '<select name ="'.$key.'">';
                foreach(array('minimal' => 1, 'low' => 25, 'medium' => 50, 'high' => 75, 'maximum' => 100) as $n => $val)
                {
                    $input .= sprintf(
                    	'<option value="%s"%s>%s (%d%%)</option>%s'
                    ,$val
                    ,(LConfiguration::get($key) == $val ? ' selected="selected"' : '') 
                    ,SLocalization::get($n)
                    ,$val
                    ,"\n");
                }
                $input .= '</select>';
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
                    $current = SAlias::getCurrent(LConfiguration::get($key));
                    $class = substr($type,2);
                    $input = sprintf("<select name=\"%s\">\n", $key);
                    if(substr($class, 0,1) == ':')
                    {
                        $class = substr($class, 1);
                        $sel = empty($current) ? ' selected="selected"' : '';
                        $input .= sprintf("\t<option value=\"\"%s>%s</option>\n",  $sel,  SLocalization::get('no_login_template'));
                    }
                    $options = BContent::getIndex($class, false);
                    
                    foreach ($options as $alias => $data) 
                    {
                        $sel = ($alias == $current) ? ' selected="selected"' : '';
                    	$input .= sprintf("\t<option value=\"%s\"%s>%s (%s)</option>\n", $alias, $sel,  htmlentities($data[0], ENT_QUOTES, 'UTF-8'), $alias);
                    }
                    $input .= "</select>\n";
                }
		}
		$label = sprintf($labeltag, SLocalization::get('key'), $key, $key, SLocalization::get($name));
		$tbl->addRow(array($label, $input));
	}
    $tbl->render();
}
?>