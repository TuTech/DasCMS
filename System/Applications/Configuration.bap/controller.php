<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH 
 * @author selke@tutech.de
 * @package org.bambuscms.applications.configuration
 * @since 2006-10-16
 * @version 1.0
 */
if(RSent::hasValue('writeconfig') && PAuthorisation::has('org.bambuscms.configuration.set'))
{
    RSent::alter('dateformat', !RSent::hasValue('dateformat') ? 'c' : RSent::get('dateformat'));
    $keys = array(
        //settings
        'sitename', 'logo','webmaster', 'copyright','cms_color','cms_text_color','dateformat',
        'logout_on_exit', 'confirm_for_exit','generator_content',  'use_wysiwyg', 'wellformed_urls',
        'login_template',
        //database_settings
        'db_server', 'db_user', 'db_password', 'db_name', 'db_table_prefix',
        
        //meta_data
        'meta_description', 'meta_keywords',
        
        'timezone', 'locale','preview_image_quality',
    
        //logs
        'logAccess', 'logChanges'
        
    );
    $checkboxes = array(
        'logout_on_exit', 
        'confirm_for_exit',  
        'use_db',
    	'wellformed_urls',
        'chdbpasswd',
        'logAccess', 
        'logChanges',
        'use_wysiwyg'
    );
    foreach ($checkboxes as $cb) 
    {
    	RSent::alter($cb, RSent::get($cb) == 'on' ? '1' : '');
    }
    
    foreach($keys as $key)
    {
        if($key != 'db_password' || RSent::hasValue('chdbpasswd'))
        {
            LConfiguration::set($key, RSent::get($key));
        }
    }
    SNotificationCenter::report('message', 'configuration_saved');
}
try
{
	$panel = WSidePanel::alloc()->init();
	$panel->setMode(
	    WSidePanel::HELPER |
	    WSidePanel::INFORMATION
    );
	//echo $panel;
}
catch(Exception $e){
	echo $e->getTraceAsString();
}
?>