<?php
require_once 'System/main.php';
PAuthentication::implied();

$se = Search_Engine::getInstance();
$se->flush();

$q = "title:+test ".time();
if(isset($argv[1]))$q = $argv[1];
$res = $se->query($q);
echo $res;

?>