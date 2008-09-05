<?php
/************************************************
* Bambus CMS 
* Created:     16. Okt 06
* License:     GNU GPL Version 2 or later (http://www.gnu.org/copyleft/gpl.html)
* Copyright:   Lutz Selke/TuTech Innovation GmbH 
* Description: 
************************************************/
if(!class_exists("Bambus"))die('No login? No bambus for you, hungry Panda!');

//some statistics of the system folder
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
            $out[] =  '<div class="listeddir"><img src="'.SPath::SYSTEM_IMAGES.'dir.png" alt="" />'.htmlentities($dir).' <span class="notreadable">'.SLocalization::get('not_readable').'</span></div>';
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
?>