<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH 
 * @package org.bambuscms.applications.configuration
 * @since 2006-10-16
 * @version 1.0
 * @author selke@tutech.de
 */

if(RSent::hasValue('writeconfig') && PAuthorisation::has('org.bambuscms.configuration.set'))
{
    RSent::alter('dateformat', !RSent::hasValue('dateformat') ? 'c' : RSent::get('dateformat'));
    $keys = array(
        //settings
        'sitename', 'logo','webmaster', 'copyright','cms_color','cms_text_color','dateformat',
        'logout_on_exit', 'confirm_for_exit','generator_content',  
          
        //database_settings
        'use_db', 'db_server', 'db_user', 'db_password', 'db_name', 'db_table_prefix',
        
        //meta_data
        'meta_description', 'meta_keywords',
        
        'timezone', 'locale',
    
        //logs
        'logAccess', 'logChanges'
        
    );
    $checkboxes = array(
        'logout_on_exit', 
        'confirm_for_exit',  
        'use_db',
        'chdbpasswd',
        'logAccess', 
        'logChanges'
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
    SNotificationCenter::report('message', 'configuration_saved-file_has_been_overwritten');
}
?>