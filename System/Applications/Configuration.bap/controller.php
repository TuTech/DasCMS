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
    if($Bambus->FileSystem->changeDir('temp'))
    {
	    $Dir = opendir ('./');  
	    while ($item = readdir ($Dir)) {
	        if((is_file($item)) && (substr($item,0,1) != '.')){
	            @unlink($item);
	        }
	    }
	    closedir($Dir);
    }
	$Bambus->FileSystem->returnToRootDir();
}

function cacheSize(){
	global $Bambus;
    $myDir = getcwd();
    $Bambus->FileSystem->changeDir('temp');
    $Dir = opendir ('./'); 
    $size = 0; 
    while ($item = readdir ($Dir)) {
        if(is_file($item)){
            $size += filesize($item);
        }
    }
    closedir($Dir);
    $Bambus->FileSystem->returnToRootDir();
    return $Bambus->formatSize($size);
}

//some statistics of the system folder
function dirlist_r($dir = './', $indent = 0){
    global $info, $Bambus;
    foreach(array('folders', 'files','size', 'php-scripts', 'php-lines', 'php-size', 'js-scripts', 'js-lines', 'js-size', 'css-scripts', 'css-lines', 'css-size') as $key)
    	if(!isset($info[$key]))$info[$key] = 0;
    $files = array();
    $dirs = array();
    $Bambus->FileSystem->changeDir('system');
    if(is_dir($dir)){
        if(@$Bambus->FileSystem->changeDir($dir, false))
        {
            $Directory = opendir ('./');
            while ($item = readdir ($Directory)) 
            {
                if(is_file($item) && (substr($item,0,1) != '.'))
                {
                    $info['files']++;
                    $suffixtemp = explode('.',strtolower($item));
                    $suffix = array_pop($suffixtemp);
                    if(in_array($suffix, array('php', 'js', 'css'))){
                        $temp = file($item);
                        $info[$suffix.'-lines'] += count($temp);
                        $info[$suffix.'-scripts']++;
                    	$info[$suffix.'-size'] += filesize($item);
                    }
                    $info['size'] += filesize($item);
                }
                elseif(is_dir($item) && (substr($item,0,1) != '.'))
                {
                    $info['folders']++;
                    $dirs[] = $item;
                }
            }
            closedir($Directory);
            
        }
        $Bambus->FileSystem->returnToRootDir();
        if($dirs != array())
        {   
            foreach($dirs as $direc)
            {
                dirlist_r($dir.$direc.'/', $indent);
            }

        }
    }
}
function pdirlist_r($dir = './', $indent = 0){
    global $out,$Bambus,$_GET;
    $files = array();
    $dirs = array();
    $yes = SLocalization::get('yes');
    $no = SLocalization::get('no');
    if(file_exists($dir)){
        if(is_readable($dir)){
            $Bambus->FileSystem->changeDir($dir, false);
            $Directory = opendir ('./');
            while ($item = readdir ($Directory)) 
            {
                if(is_file($item))// && (substr($item,0,1) != '.')
                {
                    $files[] = $item;
                }
                elseif(is_dir($item) && (substr($item,0,1) != '.'))
                {
                    $dirs[] = $item;
                }
            }
            closedir($Directory);
            $indent = $indent + 20;
        }else{
            $out[] =  '<div class="listeddir"><img src="'.$Bambus->pathTo('systemImage').'dir.png" alt="" />'.htmlentities($dir).' <span class="notreadable">'.SLocalization::get('not_readable').'</span></div>';
            $indent = $indent + 20;                
        }
        if ($files != array())
        { 
            sort ($files);
            $docpath = $Bambus->pathTo('document');
            foreach($files as $file)
            {
            	$chmod = false;
        		if(is_file($file) && !empty($_GET['_action']) && $_GET['_action'] == 'repair_rights')
        		{
        			$chmod = @chmod($file, 0666);
        		}
        		if(!$chmod)
        		{
					$fileperms = (fileperms($file));       
					$fileperms =sprintf('%o', $fileperms);
	            	if(!is_readable($file) || (!is_writable($file) && substr($file, 0, 10) == './Content/') || strpos($fileperms, '7') !== false)
	            	{
	            		$out[] = sprintf(
							'<tr><th class="left_th">%s</th><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>',
							htmlentities($file),
							is_readable($file) ? $yes : $no,
							is_writable($file) ? $yes : $no,
							substr($fileperms,3),
							htmlentities($dir)
	            		);
	            	}
        		}
            }
            
        }
        $Bambus->FileSystem->returnToRootDir();
        if($dirs != array())
        {
            foreach($dirs as $direc)
            {
                pdirlist_r($dir.$direc.'/', $indent);
            }
        }
    }
}
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
		     	SNotificationCenter::alloc()->init()->report('information', 'sorry_but_your_htaccess-file_has_been_overwritten');
	    	}
	     	$Bambus->FileSystem->write(basename($post['htaccessfile']), $htaccess);
	    }
	    elseif(!empty($post['error_404_overwrite_htaccess']) && file_exists(basename($post['htaccessfile'])))
	    {
	    	unlink(basename($post['htaccessfile']));
	    	SNotificationCenter::alloc()->init()->report('information', 'sorry_but_your_htaccess-file_has_been_deleted');
	    }
	    else
	    {
	//    	echo $post['404redirect'];
	    }
	    $ncfg->save();
	    SNotificationCenter::alloc()->init()->report('message', 'configuration_saved-file_has_been_overwritten');
	    $cfgchd = true;
	}
	$config = $Bambus->Configuration->as_array();
	
	if(!empty($post['_clear_cache']) && BAMBUS_GRP_DELETE)
	{//clear cache
	    clearCache();
	    SNotificationCenter::alloc()->init()->report('message', 'cache_cleared');
	}
?>
