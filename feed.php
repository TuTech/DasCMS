<?php
/**
 * provide rss2 
 * use SSimpleXMLWriter class to build xml
 */
require_once('./System/Component/Loader.php');
header('Content-Type: text/plain; charset=utf-8');
RSession::start();
PAuthentication::required();
try
{
    $feed = RURL::get('rss');
    $content = BContent::OpenIfPossible($feed);
    $content = BContent::Access($feed, $content);
    $pubDate = $content->getPubDate();
    if(empty($pubDate) || $pubDate > time())
    {
        header('HTTP/1.1 403 Forbidden');
    	header('Status: 403 Forbidden');
    	exit();
    }
    if($content instanceof IGeneratesFeed)
    {
        echo new FRSS2($content);
    }
}
catch(Exception $e)
{
    header('HTTP/1.1 404 Not Found');
	header('Status: 404 Not Found');
}
?>