<?php
require_once('./System/Component/Loader.php');
header('Content-Type: image/png;');
header("Expires: ".date('r', 0));
header("Cache-Control: max-age=0, public");

if(SJobScheduler::runJob())
{
    readfile(SPath::SYSTEM_IMAGES.'ok.png');
}
else
{
    readfile(SPath::SYSTEM_IMAGES.'fail.png');
}
?>