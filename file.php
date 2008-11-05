<?php
/**
 * provide file download 
 */
require_once('./System/Component/Loader.php');
RSession::start();
PAuthentication::required();
try
{
    $feed = RURL::get('get');
    $content = BContent::OpenIfPossible($feed);
    $pubDate = $content->getPubDate();
    if((empty($pubDate) || $pubDate > time()) && !PAuthorisation::has('org.bambuscms.content.cfile.view'))
    {
        header('HTTP/1.1 403 Forbidden');
    	header('Status: 403 Forbidden');
    	exit();
    }
    if($content instanceof IFileContent)
    {
        list($file, $type, $size) = $content->getDownloadMetaData();
        header("Content-Disposition: attachment; filename=\"".addslashes($file)."\"");    
        header("Content-Type: application/force-download");
        header("Content-Type: ".$type);
        header("Content-Type: application/download");
        header("Content-Description: File Transfer");             
        header("Content-Length: " . $size);
        $content->sendFileContent();
    }
    else
    {
        header('HTTP/1.1 405 Method Not Allowed');
        header('Status: 405 Method Not Allowed');
        die('405 Method Not Allowed for '.get_class($content));
    }
}
catch(Exception $e)
{
    header('HTTP/1.1 404 Not Found');
	header('Status: 404 Not Found');
}
?>