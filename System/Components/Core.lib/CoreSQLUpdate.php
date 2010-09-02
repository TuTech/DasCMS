<?php
class CoreSQLUpdate extends Core
{
	private static $verbose = false;
	const CACHE_DIR = 'SQLCache';
	protected $data = array();

	protected function parseSQLFile($file, $prefix){
		$result = array();
		$data = Core::dataFromFile($file);
		$statementDefinitions = explode('-- --', $data);

		foreach ($statementDefinitions as $def){
			$meta = array();
			$statementLines = array();
			$lines = explode("\n", $def);
			foreach ($lines as $line){
				if(substr($line,0,3) == '-- '){
					if(preg_match('/--\s+(\w+)\s*:\s*(.+)/', $line, $matches)){
						$meta[$matches[1]] = $matches[2];
					}
				}
				else{
					$statementLines[] = trim($line, " \r\n\t");
				}
			}
			$statement = trim(implode(" ", $statementLines), " \r\n\t");
			$statement = str_replace('__PFX__', $prefix, $statement);
			if(!empty ($statement) && !empty ($meta['name'])){

				//s:sql, f:number of fields, r:return, p:parameter definition, d:deterministic, m:mutable
				$result[$meta['name']] = array(
					's' => $statement,
					'f' => empty ($meta['fields']) ? 0 : intval($meta['fields']),
					'p' => empty ($meta['inputTypes']) ? '' : $meta['inputTypes'],
					'd' => empty ($meta['deterministic']) ? 0 : (strtolower($meta['deterministic']) == 'yes' ? 1 : 0)
				);
			}
		}
		return $result;
	}

	protected function readComponentData(){
		$components = array();
		$componentsDir = CMS_CLASS_PATH ;
		$DB_PREFIX = Core::settings()->getOrDefault('db_table_prefix', '');

		foreach (scandir($componentsDir) as $currentComponent){
			//read components
			$path = $componentsDir.$currentComponent;
			$suffix = Core::PACKAGE_SUFFIX;
			if(is_dir($path)
					&& strlen($currentComponent) > strlen($suffix)
					&& substr($currentComponent, strlen($suffix)*-1) == $suffix
					&& file_exists($path.Core::PACKAGE_INFO))
			{
				//component
				$components[$currentComponent] = array();

				//get contained classes
				$contens = json_decode(implode('', file($path.Core::PACKAGE_INFO)), true);

				if(is_array($contens)
						&& array_key_exists('classes', $contens)
						&& is_array($contens['classes']))
				{
					//create class cache
					foreach($contens['classes'] as $class){
						//path section from class name
						$classSubPath = Core::pathPartForClass($class);

						//build path
						$sqlFile = sprintf('%s%s/%s.sql', $componentsDir, $currentComponent, $classSubPath);
						if(file_exists($sqlFile)){
							if(self::$verbose)printf("%48s: %s\n", $class, $sqlFile);
							$data = $this->parseSQLFile($sqlFile, $DB_PREFIX);
							if(is_array($data) && count($data) > 0){
								$this->saveSQLDefinition($class, $data);
							}
						}
					}//foreach content type class
				}//if package info exists
			}//if is component
		}//foreach folder in component dir
	}//function


	protected function saveSQLDefinition($class, $data){
		$this->data[$class] = $data;
	}

	public static function run(){
		if(!is_dir('Content')){
			die("No Content Folder\n\n");
		}
		$dir = 'Content/'.self::CACHE_DIR;
		if(!is_dir($dir)){
			mkdir($dir) || die("No SQL Cache Folder\n\n");
		}
		$runner = new CoreSQLUpdate();
		$runner->readComponentData();
		foreach ($runner->data as $class => $queryData){
			Core::dataToFile(serialize($queryData), sprintf('%s/%s.gz', $dir, sha1($class)), true);
		}
	}
}
?>
