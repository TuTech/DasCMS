<?php
require_once 'System/main.php';
PAuthentication::implied();

$se = Search_Engine::getInstance();
$se->query("tag:+news Prof");


?>