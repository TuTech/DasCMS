<?php
require_once '../../System/main.php';

$cache = <<<CMF
<?php header("Content-type: text/cache-manifest"); ?>
CACHE MANIFEST

CMF;

$network = <<<CMF

   
NETWORK:
Atom.php
file.php
image.php
index.php
scheduler.php
Management/index.php
Management/localization.js.php
Management/ajaxhandler.php
CMF;

$paths = array(
	'System/Applications',
	'System/ClientData',
	'System/External'
);

$indexer = function($dir, &$indexer, &$types = null){
	$items = scandir($dir);
	$ret = array();
	foreach ($items as $item){
		if(substr($item,0,1) != '.'){
			$abs = $dir.'/'.$item;
			printf("%s\n", $abs);
			if(is_dir($abs)){
				$ret = array_merge($ret, $indexer($abs, $indexer, $types));
			}
			elseif(is_file($abs)){
				if($types == null){
					$ret[] = $abs;
				}
				elseif (is_array($types) && strpos($item, '.')) {
					$t = substr($item, strripos($item, '.')+1);
					if(in_array($t, $types)){
						$ret[] = $abs;
					}
				}
			}
		}
	}
	return $ret;
};

$cacheFiles = array();
$types = array('js', 'css', 'png', 'jpg', 'jpeg','gif');
foreach ($paths as $p){
	$cacheFiles = array_merge($cacheFiles, $indexer($p, $indexer, $types));
}
$cache .= implode("\n", $cacheFiles).$network;
Core::dataToFile($cache, 'System/ClientData/cache-manifest.php');
echo $cache."\n";
?>
