<?php
require_once '../../System/main.php';

//minify and versionate css/js files
$version = array('js' => 0, 'css' => 0);
$minifyer = function($dir, &$minifyer, &$types = null, &$version){
	$items = scandir($dir);
	$ret = array();
	$parse = array();
	foreach ($items as $item){
		if(substr($item,0,1) != '.'){
			$abs = $dir.'/'.$item;
			if(is_file($abs)){
				if (is_array($types) && strpos($item, '.')) {
					$t = substr($item, strripos($item, '.')+1);
					if(isset($types[$t])){
						$data = Core::dataFromFile($abs)."\n";
						$data = preg_replace('/[\n][ \t]+/mui', "\n", $data);
						$data = preg_replace('/[ \t]+/mui', ' ', $data);
						$data = preg_replace('/[\n]+/mui', "\n", $data);
						#doomed by single line comments
						#$data = preg_replace('/[;}{][ ]?\n/mui', ";", $data);
						$data = preg_replace('/([\[{}\]][;,]?)[\n]/mui', "$1", $data);
						$data = preg_replace('/\/\*.*\*\//muis', "", $data);

						$types[$t] .= $data;
						$version[$t] = max($version[$t], filemtime($abs));
					}
				}
			}
			elseif(is_dir($abs)){
				$parse[] = $abs;
			}
		}
	}
	foreach ($parse as $subDir){
		$ret = array_merge($ret, $minifyer($subDir, $minifyer, $types, $version));
	}
	return $ret;
};
$content = array('js' => '', 'css' => '');
$minifyer('System/ClientData', $minifyer, $content, $version);

foreach($version as $type => $timestamp){
	$v = date('Y-m-d-H-i-s', $timestamp);
	$version[$type] = $v;
	if(is_dir('Content')){
		Core::dataToFile($content[$type], 'Content/management-'.$v.'.'.$type);
	}
}
if(is_dir('Content')){
	Core::dataToJSONFile($version, 'Content/versioninfo.json');
}


//write manifest
$cache = <<<CMF
<?php header("Content-type: text/cache-manifest"); ?>
CACHE MANIFEST
Content/management-{$version['js']}.js
Content/management-{$version['css']}.css

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
	'System/Applications' => array('js', 'css', 'png', 'jpg', 'jpeg','gif'),
	'System/ClientData' =>	 array('png', 'jpg', 'jpeg','gif'),
	'System/External' =>	 array('js', 'css', 'png', 'jpg', 'jpeg','gif')
);

$indexer = function($dir, &$indexer, &$types = null){
	$items = scandir($dir);
	$ret = array();
	$parse = array();
	foreach ($items as $item){
		if(substr($item,0,1) != '.'){
			$abs = $dir.'/'.$item;
			if(is_file($abs)){
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
			elseif(is_dir($abs)){
				$parse[] = $abs;
			}
		}
	}
	foreach ($parse as $subDir){
		$ret = array_merge($ret, $indexer($subDir, $indexer, $types));
	}
	return $ret;
};

$cacheFiles = array();
foreach ($paths as $p => $types){
	$cacheFiles = array_merge($cacheFiles, $indexer($p, $indexer, $types));
}
$cache .= implode("\n", $cacheFiles).$network;
Core::dataToFile($cache, 'System/ClientData/cache-manifest.php');
echo $cache."\n";
?>
