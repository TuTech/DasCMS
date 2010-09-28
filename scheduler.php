<?php
ob_start();
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
ob_end_clean();
header('No Content', true, 204);
header('X-CMS-JobStatus: "'.addslashes($stat).'"');
?>