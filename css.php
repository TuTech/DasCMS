<?php
include('System/Component/Loader.php');
header('Content-type: text/css; charset=utf-8');
//filename will change - chache forever... almost
header("Expires: ".date('r', mktime(10,10,10,10,10,date('Y')+10)));
header("Cache-Control: max-age=290304000, public");
header("Last-Modified: ".date('r', filemtime('Content/stylesheets/default.css')));
include('Content/stylesheets/default.css');
?>