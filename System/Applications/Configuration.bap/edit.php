<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH 
 * @package org.bambuscms.applications.configuration
 * @since 2006-10-16
 * @version 1.0
 * @author selke@tutech.de
 */
if(PAuthorisation::has('org.bambuscms.configuration.set'))
{
	echo LGui::beginForm(array(), 'documentform');
}
printf('<h2>%s</h2>', SLocalization::get('system'));
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
        "confirm_for_exit"      => array("confirm_for_exit","checkbox"),
        "template_for_page_rendering"=> array("generator_content", '::CTemplate')
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
$imageSizes = array(
    'description' => 'image_size_in_description',
    'content' => 'image_size_in_content',
    'download' => 'image_size_for_download'
);
printf('<br /><h2>%s</h2>', SLocalization::get('images'));
foreach ($imageSizes as $imageSize => $imageSizeTitle) 
{
    $maintbl = new WTable(null, SLocalization::get($imageSizeTitle));
    $maintbl->setCellAlteration('','');
    $tbl = new WTable(WTable::HEADING_TOP|WTable::HEADING_LEFT);
    $restbl = new WTable(WTable::HEADING_TOP);
    
    $tbl->addRow(array('description', 'value'));
    $tbl->addRow(array('width_in_pixel', sprintf('<input type=\"text\" name=\"img_%s_width\" size=\"4\" />', $imageSize)));
    $tbl->addRow(array('height_in_pixel', sprintf('<input type=\"text\" name=\"img_%s_height\" size=\"4\" />', $imageSize)));
    $tbl->addRow(array('background-color', sprintf('#<input type=\"text\" name=\"img_%s_color\" size=\"6\" />', $imageSize)));
    $methods = array(
        'krfs' => 'keep_ratio_fixed_size',
		'kr' =>   'keep_ratio',
		'fs' =>   'fixed_size'
		
	);
	$html = '';
	foreach ($methods as $method => $title) 
	{
		$html .= sprintf(
		   "<div class=\"img_resize_method\">
				<input type=\"radio\" name=\"img_%s_method\" id=\"img_%s_%s_method\" value=\"%s\" />
				<label for=\"img_%s_%s_method\"><img src=\"./System/Images/resize_%s.png\" alt=\"%s\" title=\"%s\" /></label>
			</div>"
		   ,$imageSize
		   ,$imageSize
		   ,$method
		   ,$method
		   ,$imageSize
		   ,$method
		   ,$title
		   ,SLocalization::get($title)
		   ,SLocalization::get($title)
	   );
	}
	$restbl->addRow(array('resize-method'));
	$restbl->addRow(array($html));
	$maintbl->addRow(array($tbl, $restbl));
	$maintbl->render();
}
if(PAuthorisation::has('org.bambuscms.configuration.set'))
{
	echo LGui::hiddenInput('writeconfig','1');
	echo LGui::endForm();
}
?>