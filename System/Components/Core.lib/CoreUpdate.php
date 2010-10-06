<?php
class CoreUpdate extends Core
{
	const NO_DATABASE = false;
	const WITH_DATABASE = true;

	private static $verbose = false;
	
	public static function run($hasDatabase = true){
		self::buildIndex(self::readComponentData(), $hasDatabase);
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

							//path section from class name
							$classSubPath = Core::pathPartForClass($class);
							
							//build path
							$classFile = sprintf('%s%s/%s.php', $componentsDir, $currentComponent, $classSubPath);
							if(file_exists($classFile)){
								if(defined('DEBUG')){
									$classContent = implode('', file($classFile));
								}
								else{
									$classContent = trim(php_strip_whitespace($classFile));
								}
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

	
	private static function buildIndex($components, $hasDatabase = true){
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
		if($hasDatabase && Core::classExists('DSQL')){
			self::updateClassIndex($dblo);
		}
		else{
			self::log('<p>NO SQL Support</p>');
		}
	} 
	
	public static function updateClassIndex(array $classes)
	{
		foreach ($classes as $cname => $cguid) 
		{
			if(empty ($cguid)){
				Core::Database()
					->createQueryForClass('CoreUpdate')
					->call('updateClassIndexNoGUID')
					->withParameters($cname, $cname)
					->execute();
			}
			else{
				Core::Database()
					->createQueryForClass('CoreUpdate')
					->call('updateClassIndex')
					->withParameters($cname, $cguid, $cname, $cguid)
					->execute();
			}
		}
	}
	
	private static function log($format, $args = array()){
		if(self::$verbose)vprintf($format,$args);
	}

}
?>
