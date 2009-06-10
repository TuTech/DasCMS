<?php
/**
 * provide file download 
 */
require_once('./System/Component/Loader.php');
PAuthentication::implied();
try
{
    $file = RURL::get('get');
    SErrorAndExceptionHandler::muteErrors();
    $force_download = RURL::has('download') || LConfiguration::get('CFile_force_download') == 1;
    $extended_cache = RURL::has('extendedCache');
    if(empty($file))
    {
         $file = substr($_SERVER['PATH_INFO'],1);
         $end = min(strpos($file, '/'), strpos($file, '&'));
         if($end != 0)$file = substr($file,0, $end-1);
         if(empty($file))
         {
             throw new Exception('nothing to get', 404);
         }
    }
    try
    {
        //FIXME new WImage() is wrong - do this in an access class
        $content = BContent::Access($file, new WImage(), true);
    }
    catch (XPermissionDeniedException $e)
    {
        if(PAuthorisation::has('org.bambuscms.content.cfile.view'))
        {
            //valid user - allowed to view unpublished 
            $content = BContent::Open($alias);
        }
        else
        {
            header('HTTP/1.1 403 Forbidden');
        	header('Status: 403 Forbidden');
        	echo 'Status: 403 Forbidden';
        	exit();
        }
    }
    $cache_time = ($extended_cache) 
        ? 315360000 /* 10 years */ 
        : 86400 /* 1 day */;
    header("Expires: ".date('r', time()+$cache_time));
    header("Cache-Control: max-age=".$cache_time.", public");
    header("Content-Disposition: inline");
    header('Pragma:');//disable "Pragma: no-cache" (default for sessions) 
    $pubDate = $content->getPubDate();
    if($content instanceof IFileContent)
    {
        list($file, $type, $size) = $content->getDownloadMetaData();
        
        if($force_download)
        {
            header("Content-Disposition: attachment; charset=UTF-8; filename=\"".addslashes(mb_convert_encoding($file, 'ISO-8859-1', 'UTF-8, auto'))."\"");    
            header("Content-Type: application/force-download");
            header("Content-Type: application/download");
            header("Content-Description: File Transfer");             
        }
        else
        {
            header("Content-Disposition: inline; charset=UTF-8; filename=\"".addslashes(mb_convert_encoding($file, 'ISO-8859-1', 'UTF-8, auto'))."\"");    
        }
        header('Last-modified: '.date('r',$content->getModifyDate()));
        if(!empty($type))
        {
            header("Content-Type: ".$type);
        }
        if(!empty($size))
        {
            header(sprintf("Content-Length: %d", $size));
        }
        $content->sendFileContent();
    }
}
catch(Exception $e)
{
    header('HTTP/1.1 404 Not Found');
	header('Status: 404 Not Found');
	echo 'Status: 404 Not Found';
	echo $e->getMessage();
	echo $e->getTraceAsString();
}
?>