<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2009-03-11
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage AppController
 */
class ASystemInformation
    extends 
        BAppController 
    implements 
        IGlobalUniqueId  
{
    const GUID = 'org.bambuscms.applications.systeminformation';
        
    /**
     * @return string
     * (non-PHPdoc)
     * @see System/Component/Interface/IGlobalUniqueId#getClassGUID()
     */
    public function getClassGUID()
    {
        return self::GUID;
    }
    
    public function dirlist_r($dir = './', $indent = 0){
        global $info;
        foreach(array('folders', 'files','size', 'php-scripts', 'php-lines', 'php-size', 'js-scripts', 'js-lines', 'js-size', 'css-scripts', 'css-lines', 'css-size', 'sql-scripts', 'sql-lines', 'sql-size') as $key)
        	if(!isset($info[$key]))$info[$key] = 0;
        $files = array();
        $dirs = array();
        if($dir == './External/')
        {
            return;
        }
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
                        if(in_array($suffix, array('php', 'js', 'css','sql'))){
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
                    $this->dirlist_r($dir.$direc.'/', $indent);
                }
    
            }
        }
    }
    public function pdirlist_r($dir = './', $indent = 0){
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
                    $this->pdirlist_r($dir.$direc.'/', $indent);
                }
            }
        }
    }
    
    public function clearCache()
    {
        parent::requirePermission('org.bambuscms.system.cache.clear');
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
        SNotificationCenter::report('message', 'cache_cleared');
    }
    
    public function cacheSize(){
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
}
?>