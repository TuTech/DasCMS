<?php
require_once('./System/Component/Loader.php');
header('Content-Type: image/png;');
if(SJobScheduler::runJob())
{
    readfile(SPath::SYSTEM_IMAGES.'ok.png');
}
else
{
    readfile(SPath::SYSTEM_IMAGES.'fail.png');
}
?>