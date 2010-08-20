<?php
chdir(dirname(__FILE__));
require_once '../../System/main.php';

class CoreSQLUpdate extends Core
{
	const CACHE_DIR = 'Content/SQLCache';

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
					'd' => empty ($meta['deterministic']) ? 0 : (strtolower($meta['deterministic']) == 'yes' ? 1 : 0),
					'm' => empty ($meta['mutable']) ? 1 : (strtolower($meta['mutable']) == 'yes' ? 1 : 0),
				);
			}
		}
		return $result;
	}

	protected function locateSQLFiles(){
		if(!is_dir('Content')){
			die("\n\nPlease install the cms before running this script\n\n");
		}
		$componentsDir = CMS_CLASS_PATH;
		$DB_ENGINE = Core::settings()->getOrDefault('db_engine', 'MySQL');
		$DB_PREFIX = Core::settings()->getOrDefault('db_table_prefix', '');
		foreach (scandir($componentsDir) as $currentComponent){
			if(substr($currentComponent,0,1) == '.'){
				continue;
			}
			printf("COMPONENT: %s\n", $currentComponent);
			$sqlDir = sprintf('%s/%s/SQL/%s/', $componentsDir, $currentComponent, $DB_ENGINE);
			if(is_dir($sqlDir)){
				printf("   has SQL\n");
				foreach (scandir($sqlDir) as $sqlFile){
					if(substr($sqlFile,0,1) == '.'){
						continue;
					}
					printf("    FILE: %s\n", $sqlFile);
					if(substr(strtolower($sqlFile), -4) == '.sql'){
						$CLASS_NAME = substr($sqlFile,0,-4);
						printf("    CLASS: %s\n", $CLASS_NAME);
						$data = $this->parseSQLFile($sqlDir.'/'.$sqlFile, $DB_PREFIX);
						if(is_array($data) && count($data) > 0){
							$this->writeSQLDefinition($DB_ENGINE, $CLASS_NAME, $data);
						}
					}
				}
			}
		}
	}

	protected function writeSQLDefinition($engine, $class, $data){
		if(!is_dir(self::CACHE_DIR)){
			mkdir(self::CACHE_DIR);
		}
		if(!is_dir(self::CACHE_DIR.'/'.$engine)){
			mkdir(self::CACHE_DIR.'/'.$engine);
		}
		Core::dataToJSONFile($data, self::CACHE_DIR.'/'.$engine.'/'.$class.'.json');
	}

	public static function run(){
		$runner = new CoreSQLUpdate();
		$runner->locateSQLFiles();
	}
}
try{
	CoreSQLUpdate::run();
}
catch (Exception $e){
	echo $e->getTraceAsString();
}
?>