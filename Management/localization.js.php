<?php 
chdir('..');
require_once('./System/Component/Loader.php');
$cache_1Day = 86400;
header('Content-Type: text/javascript; charset=utf-8');
header("Expires: ".date('r', time()+$cache_1Day));
header("Cache-Control: max-age=".$cache_1Day.", public");
$allTrans = SLocalization::all();
echo 'var _ = function(k){var data = '.json_encode($allTrans).';return (data[k]) ? data[k] : k.replace(/_/g, " ");};';
?>