<?php
$_GET = array();
$_POST = array();
$_REQUEST = array();
require_once('./System/Component/Loader.php');
PAuthentication::daemonRun();
header("Expires: ".date('r', 0));
header("Cache-Control: max-age=0, public");
$stat = SJobScheduler::runJob();
if(is_bool($stat))
{
    $stat = ($stat) ? 'ok': 'stopped';
}
if(!headers_sent())
{
    header('Content-Type: image/png;');
    readfile(WIcon::pathFor($stat, 'status', WIcon::EXTRA_SMALL));
}
?>