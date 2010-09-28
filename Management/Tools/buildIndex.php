<?php
chdir(dirname(__FILE__));
require_once '../../System/main.php';
if(isset($argv[1]) && $argv[1] == 'debug'){
	define('DEBUG', true);
}
CoreUpdate::run();
?>