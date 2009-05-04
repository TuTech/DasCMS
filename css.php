<?php
include('System/Component/Loader.php');
header('Content-type: text/css; charset=utf-8');
//if the file changes, the url changes - chache very long
header("Expires: ".date('r', mktime(10,10,10,10,10,date('Y')+10)));
header("Cache-Control: max-age=290304000, public");
$file = 'default';
if(RURL::hasValue('f'))
{
    $f = basename(RURL::get('f'), '.css');
    if(file_exists('Content/stylesheets/'.$f.'.css'))
    {
        $file = $f;
    }
}
header("Last-Modified: ".date('r', filemtime('Content/stylesheets/'.$file.'.css')));
include('Content/stylesheets/'.$file.'.css');
?>