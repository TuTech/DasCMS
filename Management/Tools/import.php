<?php
chdir(dirname(__FILE__));
require_once '../../System/main.php';

$importer = new Import_Content();

for($i = 1; $i < $argc; $i++){
	if(strtolower(substr($argv[$i],0,7)) == 'http://'){
		$importer->fromURL($argv[$i]);
	}
	elseif(is_file($argv[$i])){
		$importer->fromFile($argv[$i]);
	}
}

?>