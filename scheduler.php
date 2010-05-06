<?php
$_GET = array();
$_POST = array();
$_REQUEST = array();
require_once 'System/main.php';
PAuthentication::daemonRun();
header("Expires: ".date('r', 0));
header("Cache-Control: max-age=0, public");
$stat = SJobScheduler::runJob();
if(is_bool($stat) || empty($stat))
{
    $stat = ($stat) ? 'ok': 'stopped';
}
if(!headers_sent())
{
    $icon = WIcon::pathFor($stat, 'status', WIcon::EXTRA_SMALL);
    if(file_exists($icon))
    {
        header('Content-Type: image/png;');
        readfile($icon);
    }
    else
    {
        echo $stat;
    }
}
?>