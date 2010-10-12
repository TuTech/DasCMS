<?php
$console = (!isset($_GET['argc']) && isset($argc) && $argc > 0);
if(!$console){
	ob_start();
}
$_GET = array();
$_POST = array();
$_REQUEST = array();
require_once 'System/main.php';
PAuthentication::daemonRun();

echo "\n=== SCHEDULER STARTED ===\n";
do{//infinite loop in console mode, single execution as web request
	echo "\n==> ",date('c'),"\n";
	$stat = TaskScheduler::getInstance()->runJob($console);
	var_dump($stat);
	sleep(5*$console);
}while($console);

if(!$console){
	ob_end_clean();
	header("Expires: ".date('r', 1));
	header("Cache-Control: max-age=0, public");
	header('No Content', true, 204);
	header('X-CMS-JobStatus: '.urlencode($stat));
}
?>