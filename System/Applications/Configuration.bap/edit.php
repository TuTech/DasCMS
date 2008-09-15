<?php
/************************************************
* Bambus CMS 
* Created:     16. Okt 06
* License:     GNU GPL Version 2 or later (http://www.gnu.org/copyleft/gpl.html)
* Copyright:   Lutz Selke/TuTech Innovation GmbH 
* Description: 
************************************************/
if(PAuthorisation::has('org.bambus-cms.configuration.set'))
{
	printf('<form method="post" id="documentform" name="documentform" action="%s">', SLink::link());
}
$values = array(
	"settings" => array(
		"pagetitle" => array(
			"sitename", "fullinput"
		),
		"pagelogo" => array(
			"logo", "fullinput"
		),
		"webmaster_email" => array(
			"webmaster", "fullinput"
		),
		"copyright" => array(
			"copyright", "fullinput"
		),
		"cms_color" => array(
			"cms_color", "fullinput"
		),
		"cms_text_color" => array(
			"cms_text_color", "fullinput"
		),
		"date_format" => array(
			"dateformat", "fullinput"
		),
		"logout_on_exit" => array(
			"logout_on_exit", "checkbox"
		),
		"confirm_for_exit" => array(
			"confirm_for_exit", "checkbox"
		)
	),
	/////
	"database_settings" => array(
		"use_database" => array(
			"use_db", "checkbox"
		),
		"server" => array(
			"db_server", "fullinput"
		),
		"user" => array(
			"db_user", "fullinput"
		),
		"password" => array(
			"db_password", "password"
		),
		"database_name" => array(
			"db_name", "fullinput"
		),
		"database_table_prefix" => array(
			"db_table_prefix", "fullinput"
		)
	),
	"meta_data" => array(
		"meta_keywords" => array(
			"meta_keywords", "fullinput"
		),
		"meta_description" => array(
			"meta_description", "fullinput"
		)
	),
	"logs" => array(
		"page_changes" => array(
			"logChanges", "checkbox"
		)
	),
);
foreach($values as $title => $settings)
{
	$flip = 2;
	printf('<h3>%s</h3>', SLocalization::get($title));
	echo '<table cellspacing="0" class="borderedtable full">';
	printf('<tr><th>%s</th><th>%s</th><th>%s</th></tr>', SLocalization::get('description'), SLocalization::get('value'), SLocalization::get("configuration_keys"));
	
	foreach($settings as $name => $options)
	{
		$flip = ($flip == 1) ? 2 : 1;
		if($options[1] == 'fullinput')
		{
			$text = '<input class="fullinput" type="text" size="40" name="%s" id="%s" value="%s" />';
			$input = sprintf($text, $options[0], $options[0], htmlentities(LConfiguration::get($options[0])));
		}
		elseif($options[1] == 'checkbox')
		{
			$text = '<input type="checkbox" name="%s" id="%s" %s/>';
			$input = sprintf($text, $options[0], $options[0], (LConfiguration::get($options[0]) != '1') ? '' : ' checked="checked"');		
		}
		elseif($options[1] == 'password')
		{
			$input = sprintf('<input class="fullinput" type="password" size="40" name="db_password" value="%s" /><br /><input type="checkbox" name="chdbpasswd" />%s', (trim(LConfiguration::get('db_password')) != '') ? '#######' : '' , SLocalization::get('change_db_password'));
		}
		printf('<tr class="flip_%s"><th scope="row"><label for="%s">%s</label></th><td>%s</td><td class="tdx100">%s</td></tr>', $flip, $options[0], SLocalization::get($name), $input,  (!empty($options[2])) ? '' : '{'.$options[0].'}');
	}
	echo '</table><br />';

}
if(PAuthorisation::has('org.bambus-cms.configuration.set'))
{
	echo '<input type="hidden" name="writeconfig" value="1" /></form>';
}
?>
