<?php
/**
 * provide file download 
 */
require_once('./System/Component/Loader.php');
RSession::start();
PAuthentication::required();
try
{
    $file = RURL::get('get');
    SErrorAndExceptionHandler::muteErrors();
    $force_download = RURL::has('download');
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
    $content = BContent::OpenIfPossible($file);
    if(!PAuthorisation::has('org.bambuscms.content.cfile.view'))
    {
        $content = BContent::Access($file, $content);
        $pubDate = $content->getPubDate();
        if((empty($pubDate) || $pubDate > time()))
        {
            header('HTTP/1.1 403 Forbidden');
        	header('Status: 403 Forbidden');
        	echo 'Status: 403 Forbidden';
        	exit();
        }
    }
    if($content instanceof IFileContent)
    {
        list($file, $type, $size) = $content->getDownloadMetaData();
        
        if($force_download)
        {
            header("Content-Disposition: attachment; filename=\"".addslashes($file)."\"");    
            header("Content-Type: application/force-download");
            header("Content-Type: application/download");
            header("Content-Description: File Transfer");             
        }
        else
        {
            header("Content-Disposition: inline; filename=\"".addslashes($file)."\"");    
            
        }
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