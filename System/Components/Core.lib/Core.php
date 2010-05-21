<?php
class Core
{
	const GUID_LOOKUP_FILE = 'GUID_Lookup.json';
	const PACKAGE_INFO = '/contents.json';
	const PACKAGE_SUFFIX = '.lib';

	/**
	 * @var array
	 */
	private static $interfaceLookup = array();
	private static $GUIDLookup = null;
	private static $settings = null;
	
	/**
	 * @return Settings
	 */
	public static function settings(){
		if(self::$settings == null){
			self::$settings = new Settings();
		}
		return self::$settings;
	}

	public static function getClassCachePath($class){
		return self::getCachePath($class).'.php';
	}
	
	/**
	 * @param string $class
	 * @return void
	 */
	public static function loadClass($class){
		class_exists($class, true);//let the autoloader do its job
	}
	
	/**
	 * is the class available in this installation
	 * @param $class
	 * @return bool
	 */
	
	public static function classExists($class){
		return file_exists(self::getClassCachePath($class));
	}
	
	/**
	 * get a list of classes implementing the given interface
	 * @param string $interface
	 * @return array
	 */
	public static function getClassesWithInterface($interface){
		//read interface customers json interface_id.json
		if(!isset(self::$interfaceLookup[$interface])){
			self::$interfaceLookup[$interface] = array();
			$file = self::getCachePath($interface).'.json';
			$data = self::dataFromJSONFile($file);
			if(!empty($data) && is_array($data)){
				self::$interfaceLookup[$interface] = $data;
			}
		}
		return self::$interfaceLookup[$interface];
	}
	
	/**
	 * resolve class guid
	 * @param string $GUID
	 * @return string
	 */
	public static function getClassNameForGUID($GUID){
		$class = null;
		if(self::$GUIDLookup == null){
			self::$GUIDLookup = self::dataFromJSONFile(constant('CMS_CLASS_CACHE_PATH').self::GUID_LOOKUP_FILE);
		}
		if(isset(self::$GUIDLookup[$GUID])){
			$class = self::$GUIDLookup[$GUID];
		}
		return $class;
	}

	public static function isImplementation($class, $interface)
	{
		$className = (is_object($class)) ? get_class($class) : $class;
		if(!Core::classExists($className)){
			throw new XUndefinedIndexException('Class not found');
		}
		return in_array($interface, class_implements($className, true));
	}
	
	protected static function storeInterfaceLookup(array $interfaceLookup){
		foreach ($interfaceLookup as $interface => $classes){
			if(file_exists(self::getClassCachePath($interface))){
				$file = self::getCachePath($interface).'.json';
				self::dataToJSONFile($interfaceLookup[$interface], $file);
			}
		}
	}
	
	protected static function storeGUIDLookup(array $guidLookup){
		$lookup = array();
		foreach ($guidLookup as $guid => $class){
			if(file_exists(self::getClassCachePath($class))){
				$lookup[$guid] = $class;
			}
		}
		self::dataToJSONFile($lookup, constant('CMS_CLASS_CACHE_PATH').self::GUID_LOOKUP_FILE);
	}
	
	/**
	 * 
	 * @param unknown_type $class
	 */
	private static function getCachePath($class){
		return constant('CMS_CLASS_CACHE_PATH').strtoupper(sha1($class));
	}
	
	/**
	 * read data objects
	 * @param unknown_type $file
	 */
	public static function dataFromJSONFile($file){
		$data = null;
		if(file_exists($file)){
			$data = implode('',file($file));
			$data = @json_decode($data, true);
		}
		return $data;
	}
	
	/**
	 * store data objects
	 * @param unknown_type $data
	 * @param unknown_type $file
	 */
	public static function dataToJSONFile($data, $file){
		self::dataToFile(json_encode($data), $file);
	}

	public static function dataToFile($data, $file){
		$t = tempnam(CMS_TEMP, 'Core_tmp_');
		$fp = fopen($t, 'w+');
		fwrite($fp, $data);
		fclose($fp);

		rename($t, $file);
	}
	
	//lock instances
	protected function __construct(){}
	private function __clone(){}
}
?>