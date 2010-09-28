<?php
require_once 'System/main.php';
PAuthentication::implied();

$se = Search_Engine::getInstance();
$q = "title:+test ".time();
if(isset($argv[1]))$q = $argv[1];
$se->query($q);


?>