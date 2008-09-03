<?php
/************************************************
 * Bambus CMS
 * Created:     16. Okt 06
 * License:     GNU GPL Version 2 or later (http://www.gnu.org/copyleft/gpl.html)
 * Copyright:   Lutz Selke/TuTech Innovation GmbH
 * Description:
 ************************************************/
if(!class_exists("Bambus"))die('No login? No bambus for you, hungry Panda!');
function clearCache(){
    global $Bambus;
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
    global $Bambus;
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

function getData($key, $GPCarray)
{
    $data = isset($GPCarray[$key]) ? $GPCarray[$key] : '';
    return (get_magic_quotes_gpc()) ? stripslashes($data) : $data;
}
/////////////////////////
//save and process data//
/////////////////////////

if(isset($post['writeconfig']) && BAMBUS_GRP_EDIT)
{//save
$ncfg = &$Bambus->Configuration;
//die('config unsaveable');
$post['htaccessfile'] = (empty($post['htaccessfile'])) ? '.htaccess' : $post['htaccessfile'];
 
$alt_cms_uri = dirname($_SERVER['REQUEST_URI'].'');
$temp = explode("/", $alt_cms_uri);
array_pop($temp);
$alt_cms_uri = 'http://'.$_SERVER['SERVER_NAME'].implode("/", $temp).'/';
$cms_root_path = implode("/", $temp);
 
$post['dateformat'] = (empty($post['dateformat'])) ? 'd.m.Y H:i:s' : $post['dateformat'];
$keys = array('sitename', 'logout_on_exit', 'confirm_for_exit', '404redirect', 'htaccessfile', 'logo', 'dateformat', 'cms_uri', 'cms_color','cms_text_color', 'webmaster', 'use_db', 'autoBackup', 'logAccess', 'logChanges', 'copyright', 'meta_description', 'meta_keywords', 'db_server', 'db_user', 'db_password', 'db_name', 'db_table_prefix');
foreach($keys as $key)
{
    if($key != 'db_password' || !empty($post['chdbpasswd']))
    {
        $post[$key] = (isset($post[$key])) ? $post[$key] : '';
        $post[$key] = ($post[$key] != 'on') ? $post[$key] : '1';
        $Bambus->Configuration->set($key, trim($post[$key]));
        $ncfg->set($key, trim($post[$key]));
    }
}
//TODO: 404 redirect is out of order
if(!empty($post['404redirect']))
{
    $htaccess = "ErrorDocument 404 ".$cms_root_path."/index.php";
    if(!empty($post['error_404_overwrite_htaccess']) && file_exists(basename($post['htaccessfile'])))
    {
        SNotificationCenter::report('information', 'sorry_but_your_htaccess-file_has_been_overwritten');
    }
    DFileSystem::Save(basename($post['htaccessfile']), $htaccess);
}
elseif(!empty($post['error_404_overwrite_htaccess']) && file_exists(basename($post['htaccessfile'])))
{
    unlink(basename($post['htaccessfile']));
    SNotificationCenter::report('information', 'sorry_but_your_htaccess-file_has_been_deleted');
}
else
{
    //    	echo $post['404redirect'];
}
$ncfg->save();
SNotificationCenter::report('message', 'configuration_saved-file_has_been_overwritten');
$cfgchd = true;
}
$config = $Bambus->Configuration->as_array();

if(!empty($post['_clear_cache']) && BAMBUS_GRP_DELETE)
{//clear cache
clearCache();
SNotificationCenter::report('message', 'cache_cleared');
}
?>
