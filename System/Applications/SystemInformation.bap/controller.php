<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH 
 * @author selke@tutech.de
 * @package org.bambuscms.applications.systeminformation
 * @since 2006-10-16
 * @version 1.0
 */
function dirlist_r($dir = './', $indent = 0){
    global $info;
    foreach(array('folders', 'files','size', 'php-scripts', 'php-lines', 'php-size', 'js-scripts', 'js-lines', 'js-size', 'css-scripts', 'css-lines', 'css-size') as $key)
    	if(!isset($info[$key]))$info[$key] = 0;
    $files = array();
    $dirs = array();
    chdir(SPath::SYSTEM);
    if(is_dir($dir)){
        if(@chdir($dir))
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
        chdir(constant('BAMBUS_CMS_ROOTDIR'));
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
    global $out;
    $files = array();
    $dirs = array();
    $yes = SLocalization::get('yes');
    $no = SLocalization::get('no');
    if(file_exists($dir)){
        if(is_readable($dir)){
            if(@chdir($dir))
            {
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
            }
        }else{
            $out[] =  array(htmlentities($dir), SLocalization::get('not_readable'), '-', '-', '-');
            $indent = $indent + 20;                
        }
        if ($files != array())
        { 
            sort ($files);
            foreach($files as $file)
            {
            	$chmod = false;
        		if(is_file($file) && RURL::get('_action') == 'repair_rights')
        		{
        			$chmod = @chmod($file, 0666);
        		}
        		if(!$chmod)
        		{
					$fileperms = (fileperms($file));       
					$fileperms =sprintf('%o', $fileperms);
	            	if(!is_readable($file) || (!is_writable($file) && substr($file, 0, 10) == './Content/') || strpos($fileperms, '7') !== false)
	            	{
                        $out[] = array(
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
        chdir(constant('BAMBUS_CMS_ROOTDIR'));
        if($dirs != array())
        {
            foreach($dirs as $direc)
            {
                pdirlist_r($dir.$direc.'/', $indent);
            }
        }
    }
}
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
if(RURL::get('action') == '_clear_cache' && PAuthorisation::has('org.bambuscms.cache.clear'))
{
    //clear cache
    clearCache();
    SNotificationCenter::report('message', 'cache_cleared');
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