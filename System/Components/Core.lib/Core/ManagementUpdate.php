<?php
class Core_ManagementUpdate extends Core
{
	private static $version = array('js' => 0, 'css' => 0);
	private static $content = array('js' => '', 'css' => '');

	//minify and versionate css/js files

	private static function minifyer($dir, &$types, &$version, $jsmin){
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
			self::minifyer($subDir, $types, $version, $jsmin);
		}
	}

	private static function indexer($dir, &$types = null){
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
			$ret = array_merge($ret, self::indexer($subDir, $types));
		}
		return $ret;
	}

	public static function run(){
		//load css/js data
		self::minifyer('System/ClientData', self::$content, self::$version, Core::classExists('JSMin') && class_exists('JSMin', true));

		//if has versioninfo read filenames
		$oldVersionInfo = file_exists('Content/versioninfo.json') ? Core::dataFromJSONFile('Content/versioninfo.json') : array();
		foreach ($oldVersionInfo as $file){
			unlink($file);
		}
		
		//build cache files
		$versioninfo = array();
		$expires = 60 * 60 * 24 * 365 * 10;//10 years
		$tz = Core::settings()->getOrDefault('timezone', 'UTC');
		$expireHeaders = "date_default_timezone_set('".$tz."');".
						"header('Cache-Control:max-age=".$expires.", public');".
						"header('Expires:Fri, '.date('r', time()+".$expires.")); ";
		$headers = array(
			'js' =>  "<?php header( 'Content-Type: application/javascript' ); ".$expireHeaders."?>\n",
			'css' => "<?php header( 'Content-Type: text/css' ); ".$expireHeaders."?>\n"
		);

		//write cache files
		foreach(self::$version as $type => $timestamp){
			$v = date('Y-m-d-H-i-s', $timestamp);
			$versioninfo[$type] = 'Content/management-'.$type.'-'.$v.'.php';
			if(is_dir('Content')){
				Core::dataToFile($headers[$type].self::$content[$type], $versioninfo[$type]);
			}
		}
		Core::dataToJSONFile($versioninfo, 'Content/versioninfo.json');

		//construct cache manifest
		$cache = '<?php header("Content-type: text/cache-manifest"); ?>'.
				"CACHE MANIFEST\n".
				$versioninfo['js']."\n".
				$versioninfo['css']."\n";
		$network = "\nNETWORK:\n*";

		//index other media
		$paths = array(
			'System/Applications' => array('js', 'css', 'png', 'jpg', 'jpeg','gif'),
			'System/ClientData' =>	 array('png', 'jpg', 'jpeg','gif'),
			'System/External' =>	 array('js', 'css', 'png', 'jpg', 'jpeg','gif')
		);
		$cacheFiles = array();
		foreach ($paths as $p => $types){
			$cacheFiles = array_merge($cacheFiles, self::indexer($p, $types));
		}

		//combine and write cache manifest
		$cache .= implode("\n", $cacheFiles).$network;
		Core::dataToFile($cache, 'Content/cache-manifest.php');
	}
}
?>