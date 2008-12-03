<?php
$_GET = array();
$_POST = array();
$_REQUEST = array();
require_once('./System/Component/Loader.php');
PAuthentication::daemonRun();
header("Expires: ".date('r', 0));
header("Cache-Control: max-age=0, public");
$img = (SJobScheduler::runJob()) ? 'ok.png': 'fail.png';
if(!headers_sent())
{
    header('Content-Type: image/png;');
    readfile(SPath::SYSTEM_IMAGES.$img);
}
?>