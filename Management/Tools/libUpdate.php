<?php
//create contents.json from given comp
chdir(dirname(__FILE__));
require_once '../../System/main.php';

class CoreIndex extends Core
{
	public static function run(){
		$componentsDir = CMS_CLASS_PATH ;
		$suffix = Core::PACKAGE_SUFFIX;

		foreach (scandir($componentsDir) as $currentComponent){
			//read components
			$path = $componentsDir.$currentComponent;
			$componentDefinitionFile = $path.Core::PACKAGE_INFO;

			$packageFiles = array();

			if(is_dir($path)
					&& strlen($currentComponent) > strlen($suffix)
					&& substr($currentComponent, strlen($suffix)*-1) == $suffix)
			{
				chdir($path);
				foreach (scandir('.') as $item){
					if(substr($item,0,1) != '.'
							&& $item != basename($componentDefinitionFile))
					{
						if(is_file($item)){
							$packageFiles[] = $item;
						}
						elseif(is_dir($item)){
							$subfiles = self::getSubFiles($item);
							foreach ($subfiles as $sf){
								$packageFiles[] = $sf;
							}
						}
					}
				}
				$packageInfo = array(
					"classes" => array(),
					"interfaces" => array(),
					"undefined" => array(),
					"invokeOnLaunch" => array(),
					"invokeOnEvent" => array()
				);
				echo "<h2>\n\t".$path."\n</h2>\n<pre>\n\n";
				foreach ($packageFiles as $pf){
					printf("%s\n\ttype:\t\t%s\n\tclassName:\t%s\n\n", $pf, self::discoverType($pf), self::makeClassName($pf));
					if(substr(strtolower($pf), -4) == '.php'){
						$packageInfo[self::discoverType($pf)][] = self::makeClassName($pf);
					}
				}
				Core::dataToJSONFile($packageInfo, $componentDefinitionFile);
				echo "</pre>\n";
			}
		}
	}

	protected static function discoverType($file){
		$type = 'undefined';
		$data = '';
		$fp = fopen($file, 'r');
		if($fp){
			$data = fread($fp, 4096);
			fclose($fp);
		}
		$data = strtolower($data);
		if(strpos($data, 'class', 4) !== false){
			$type = 'classes';
		}
		elseif(strpos($data, 'interface', 4) !== false){
			$type = 'interfaces';
		}
		return $type;
	}

	protected static function makeClassName($fileName){
		$fileName = substr($fileName, 0, -4);//remove ".php"
		$parts = explode('/', $fileName);

		$finalPart = array_pop($parts);
		$isAbstract = substr($finalPart, 0, 1) == '_';
		if($isAbstract){
			$finalPart = substr($finalPart,1);
		}

		$nameSpaced = '';
		foreach ($parts as $part){
			if(preg_match('/^_([.]*)_$/', $part, $match)){
				$nameSpaced .= $match[1].'\\';
			}
			else{
				$nameSpaced .= $part.'_';
			}
		}
		$nameSpaced .= $finalPart;
		if($isAbstract){
			$nsparts = explode('\\', $nameSpaced);
			$nsparts[] = '_'.array_pop($nsparts);
			$nameSpaced = implode('\\', $nsparts);
		}

		return $nameSpaced;
	}

	protected static function getSubFiles($dir){
		$ret = array();
		
		foreach (scandir($dir) as $item){
			$path = $dir.'/'.$item;
			if(substr($item,0,1) != '.'){
				if(is_file($path)){
					$ret[] = $path;
				}
				elseif(is_dir($path)){
					$subfiles = self::getSubFiles($path);
					foreach ($subfiles as $sf){
						$ret[] = $sf;
					}
				}
			}
		}
		return $ret;
	}
}

CoreIndex::run();
?>