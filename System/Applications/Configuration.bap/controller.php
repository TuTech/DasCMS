<?php
/************************************************
 * Bambus CMS
 * Created:     16. Okt 06
 * License:     GNU GPL Version 2 or later (http://www.gnu.org/copyleft/gpl.html)
 * Copyright:   Lutz Selke/TuTech Innovation GmbH
 * Description:
 ************************************************/
if(RSent::hasValue('writeconfig') && PAuthorisation::has('org.bambus-cms.configuration.set'))
{
    RSent::alter('dateformat', !RSent::hasValue('dateformat') ? 'c' : RSent::get('dateformat'));
    $keys = array('sitename', 'logout_on_exit', 'confirm_for_exit',  
                    'logo', 'dateformat', 'cms_uri', 'cms_color','cms_text_color', 'webmaster', 'use_db', 
                     'autoBackup', 'logAccess', 'logChanges', 'copyright', 'meta_description', 'meta_keywords', 
                     'db_server', 'db_user', 'db_password', 'db_name', 'db_table_prefix');
    foreach($keys as $key)
    {
        if($key != 'db_password' || RSent::hasValue('chdbpasswd'))
        {
            
            RSent::alter($key, RSent::get($key) == 'on' ? '1' : RSent::get($key));
            LConfiguration::set($key, trim(RSent::get($key)));
        }
    }
    SNotificationCenter::report('message', 'configuration_saved-file_has_been_overwritten');
}
?>