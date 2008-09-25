<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH 
 * @package org.bambus-cms.applications.configuration
 * @since 2006-10-16
 * @version 1.0
 * @author selke@tutech.de
 */
if(PAuthorisation::has('org.bambus-cms.configuration.set'))
{
	echo LGui::beginForm(array(), 'documentform');
}
$values = array(
	"settings" => array(
        "pagetitle"             => array("sitename",        "fullinput"),
        "pagelogo"              => array("logo",            "fullinput"),
        "webmaster_email"       => array("webmaster",       "fullinput"),
        "copyright"             => array("copyright",       "fullinput"),
        "cms_color"             => array("cms_color",       "fullinput"),
        "cms_text_color"        => array("cms_text_color",  "fullinput"),
        "date_format"           => array("dateformat",      "fullinput"),
        "logout_on_exit"        => array("logout_on_exit",  "checkbox"),
        "confirm_for_exit"      => array("confirm_for_exit","checkbox")
	),
	/////
	"database_settings" => array(
        "use_database"          => array("use_db",          "checkbox"),
        "server"                => array("db_server",       "fullinput"),
        "user"                  => array("db_user",         "fullinput"),
        "password"              => array("db_password",     "password"),
        "database_name"         => array("db_name",         "fullinput"),
        "database_table_prefix" => array("db_table_prefix", "fullinput")
	),
	"meta_data" => array(
        "meta_keywords"         => array("meta_keywords",   "fullinput"),
        "meta_description"      => array("meta_description","fullinput")
	),
	"logs" => array(
        "page_changes"          => array("logChanges",      "checkbox")
	),
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
		}
		$label = sprintf($labeltag, $key, $key, SLocalization::get($name));
		$tbl->addRow(array($label, $input));
	}
    $tbl->render();
}
if(PAuthorisation::has('org.bambus-cms.configuration.set'))
{
	echo LGui::hiddenInput('writeconfig','1');
	echo LGui::endForm();
}
?>