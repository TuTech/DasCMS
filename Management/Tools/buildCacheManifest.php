<?php
require_once '../../System/main.php';

//minify and versionate css/js files
$version = array('js' => 0, 'css' => 0);
$minifyer = function($dir, &$minifyer, &$types, &$version, $jsmin){
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
						$ret[strtoupper($item).$item] = array($abs, $t);
					}
				}
			}
			elseif(is_dir($abs)){
				$parse[] = $abs;
			}
		}
	}
	ksort($ret);

	foreach($ret as $abst){
		list($abs, $t) = $abst;
		$data = Core::dataFromFile($abs)."\n";
		if($jsmin && $t == 'js'){
			$data = JSMin::minify($data);
		}
		else{
			$data = preg_replace('/[\n][ \t]+/mui', "\n", $data);
			$data = preg_replace('/[ \t]+/mui', ' ', $data);
			$data = preg_replace('/[\n]+/mui', "\n", $data);
		}

		$types[$t] .= $data;
		$version[$t] = max($version[$t], filemtime($abs));
	}
	foreach ($parse as $subDir){
		$minifyer($subDir, $minifyer, $types, $version, $jsmin);
	}
};

$content = array('js' => '', 'css' => '');
$minifyer('System/ClientData', $minifyer, $content, $version, Core::classExists('JSMin') && class_exists('JSMin', true));

$versioninfo = array();
foreach($version as $type => $timestamp){
	$v = date('Y-m-d-H-i-s', $timestamp);
	$versioninfo[$type] = 'Content/management-'.$v.'.'.$type;
	if(is_dir('Content')){
		Core::dataToFile($content[$type], $versioninfo[$type]);
	}
}

//write manifest
$cache = <<<CMF
<?php header("Content-type: text/cache-manifest"); ?>
CACHE MANIFEST
{$versioninfo['js']}
{$versioninfo['css']}

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
if(is_dir('Content')){
	Core::dataToJSONFile($versioninfo, 'Content/versioninfo.json');
	Core::dataToFile($cache, 'Content/cache-manifest.php');
}
echo $cache."\n";
?>
