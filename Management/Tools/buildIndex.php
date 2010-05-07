<?php
require_once '../../System/main.php';

class CoreUpdate extends Core
{
	private static $verbose = false;
	
	public static function run(){
		self::buildIndex(self::readComponentData());
	}

	private static function readComponentData(){
		$components = array();
		$componentsDir = CMS_CLASS_PATH ;

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
				
				foreach(array('classes', "interfaces") as $contentType){
					if(is_array($contens) 
							&& array_key_exists($contentType, $contens) 
							&& is_array($contens[$contentType]))
					{
						//create class cache
						foreach($contens[$contentType] as $class){

							//build path//
							$classCachePath = Core::getClassCachePath($class);
							
							//spilt namespace parts
							$namespaceParts = explode('\\', $class);
							
							//extract class
							$className = array_pop($namespaceParts);
							
							//apply special folder naming for namespace parts
							$pathComponents = array();
							foreach ($namespaceParts as $nsp){
								$pathComponents[] = sprintf('_%s_', $nsp);
							}
							$namespaceParts = $pathComponents;

							$isAbstract = substr($className,0,1) == '_';

							//folders for class inheritance tree
							$className = str_replace('_', '/', $className);//Foo_Bar -> Foo/Bar

							//abstract classes with '_'-prefix gets this prefix for the php-file
							if($isAbstract){
								$classParts = explode('/', $className);
								$classParts[] = '_'.array_pop($classParts);
								$className = implode('/', $classParts);
							}

							//combine namespace and class
							$namespaceParts[] = $className;
							
							//build path
							$classFile = sprintf('%s%s/%s.php', $componentsDir, $currentComponent, implode('/', $namespaceParts));
							if(file_exists($classFile)){
								$classContent = trim(php_strip_whitespace($classFile));

								//write minified class
								$fp = fopen($classCachePath, 'w+');
								fwrite($fp, $classContent);
								fclose($fp);

								//save class in index
								$components[$currentComponent][] = $class;
							}
						}//foreach content type class
					}//if package info exists
				}//foreach content type
			}//if is component
		}//foreach folder in component dir
		
		return $components;
	}//function

	
	private static function buildIndex($components){
		try{
			self::log('<h1>testing...</h1>');
			$iflo = array();
			$guidlo = array();
			$dblo = array();
			foreach($components as $component){
				foreach($component as $class){
					$guid = '';
					if(class_exists($class, true)){
						self::log('<p>[c] <b>%s</b>', $class);
						$dblo[$class] = '';
						if(defined($class.'::GUID')){
							$guid = constant($class.'::GUID');
							if(!empty($guid)){
								$guidlo[$guid] = $class;
								$dblo[$class] = $guid;
							}
							self::log(' <code>(GUID: "%s")</code>', $guid);
						}
						$impl = class_implements($class);
						foreach ($impl as $implemented){
							if(!isset($iflo[$implemented])){
								$iflo[$implemented] = array();
							} 
							$iflo[$implemented][] = $class;
						}
					}
					elseif(interface_exists($class, true)){
						self::log('<p>[i] <b>%s</b>', $class);
					}
					else{
						self::log('<h1 style="color:red">FAIL</h1>');
					}
					self::log('</p>');
					
				}
			}
		}catch (Exception $e){
			echo $e;
		}
		Core::storeInterfaceLookup($iflo);
		Core::storeGUIDLookup($guidlo);
		
		if(Core::classExists('DSQL')){
			self::updateClassIndex($dblo);
		}
		else{
			self::log('<p>NO SQL Support</p>');
		}
	} 
	
	public static function updateClassIndex(array $classes)
	{
		$DB = call_user_func_array(array('DSQL', 'getSharedInstance'), array());
		if(empty($DB) || !is_object($DB)){
			return;
		}
		$sql = 
			"INSERT INTO 
				Classes 
					(class, guid)
				VALUES 
					('%s',%s)
				ON DUPLICATE KEY UPDATE 
					class = '%s',
					guid = %s";
		$ci = array();
		foreach ($classes as $cname => $cguid) 
		{
			$cn = $DB->escape($cname);
			$cg = empty($cguid) ? 'NULL' : '"'.$DB->escape($cguid).'"';
			$DB->queryExecute(sprintf($sql, $cn, $cg, $cn, $cg));
		}
	}
	
	private static function log($format, $args = array()){
		if(self::$verbose)vprintf($format,$args);
	}

}//class
try{
	CoreUpdate::run();
}
catch (Exception $e){
	echo $e->getTraceAsString();
}
?>