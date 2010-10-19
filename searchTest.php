<?php
require_once 'System/main.php';
PAuthentication::implied();

$se = Controller_Search_Engine::getInstance();
$se->flush();

$q = "title:+test ".time();
if(isset($argv[1]))$q = $argv[1];
$res = $se->query($q);
echo $res;

?>