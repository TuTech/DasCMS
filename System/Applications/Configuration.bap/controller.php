<?php
/************************************************
 * Bambus CMS
 * Created:     16. Okt 06
 * License:     GNU GPL Version 2 or later (http://www.gnu.org/copyleft/gpl.html)
 * Copyright:   Lutz Selke/TuTech Innovation GmbH
 * Description:
 ************************************************/
function clearCache(){
    $myDir = getcwd();
    if(chdir(SPath::TEMP))
    {
        $Dir = opendir ('./');
        while ($item = readdir ($Dir)) {
            if((is_file($item)) && (substr($item,0,1) != '.')){
                @unlink($item);
            }
        }
        closedir($Dir);
    }
    chdir(constant('BAMBUS_CMS_ROOTDIR'));
}

function cacheSize(){
    $myDir = getcwd();
    chdir(SPath::TEMP);
    $Dir = opendir ('./');
    $size = 0;
    while ($item = readdir ($Dir)) {
        if(is_file($item)){
            $size += filesize($item);
        }
    }
    closedir($Dir);
    chdir(constant('BAMBUS_CMS_ROOTDIR'));
    return DFileSystem::formatSize($size);
}

//some statistics of the system folder

/////////////////////////
//save and process data//
/////////////////////////

if(RSent::hasValue('writeconfig') && BAMBUS_GRP_EDIT)
{//save
    //die('config unsaveable');
    RSent::alter('htaccessfile', RSent::get('htaccessfile') == '' ? '.htaccess' : RSent::get('htaccessfile'));
     
    $alt_cms_uri = dirname($_SERVER['REQUEST_URI'].'');
    $temp = explode("/", $alt_cms_uri);
    array_pop($temp);
    $alt_cms_uri = 'http://'.$_SERVER['SERVER_NAME'].implode("/", $temp).'/';
    $cms_root_path = implode("/", $temp);
     
    RSent::alter('dateformat', RSent::hasValue('dateformat') ? 'd.m.Y H:i:s' : RSent::get('dateformat'));
    $keys = array('sitename', 'logout_on_exit', 'confirm_for_exit', '404redirect', 'htaccessfile', 'logo', 'dateformat', 'cms_uri', 'cms_color','cms_text_color', 'webmaster', 'use_db', 'autoBackup', 'logAccess', 'logChanges', 'copyright', 'meta_description', 'meta_keywords', 'db_server', 'db_user', 'db_password', 'db_name', 'db_table_prefix');
    foreach($keys as $key)
    {
        if($key != 'db_password' || RSent::get('chdbpasswd') != '')
        {
            
            RSent::alter($key, RSent::get($key) == 'on' ? '1' : RSent::get($key));
            LConfiguration::set($key, trim(RSent::get($key)));
        }
    }
    //TODO: 404 redirect is out of order
    if(RSent::hasValue('404redirect'))
    {
        $htaccess = "ErrorDocument 404 ".$cms_root_path."/index.php";
        if(RSent::hasValue('error_404_overwrite_htaccess') && file_exists(basename(RSent::get('htaccessfile'))))
        {
            SNotificationCenter::report('information', 'sorry_but_your_htaccess-file_has_been_overwritten');
        }
        DFileSystem::Save(basename(RSent::get('htaccessfile')), $htaccess);
    }
    elseif(RSent::hasValue('error_404_overwrite_htaccess') && file_exists(basename(RSent::get('htaccessfile'))))
    {
        unlink(basename(RSent::get('htaccessfile')));
        SNotificationCenter::report('information', 'sorry_but_your_htaccess-file_has_been_deleted');
    }
    SNotificationCenter::report('message', 'configuration_saved-file_has_been_overwritten');
    $cfgchd = true;
}

if(RSent::hasValue('_clear_cache') && BAMBUS_GRP_DELETE)
{
    //clear cache
    clearCache();
    SNotificationCenter::report('message', 'cache_cleared');
}
?>
