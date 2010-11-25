<?php
class Core
{
	const GUID_LOOKUP_FILE = 'GUID_Lookup.json';
	const PACKAGE_INFO = '/contents.json';
	const PACKAGE_SUFFIX = '.lib';

	const PATH_SYSTEM = './System/';
	const PATH_SYSTEM_CLIENT_DATA = './System/ClientData/';
	const PATH_SYSTEM_ICONS = './System/ClientData/Icons/tango/';
    const PATH_SYSTEM_IMAGES = './System/ClientData/Images/';
    const PATH_SYSTEM_TEMPLATES = './System/Resource/Templates/';
    const PATH_SYSTEM_APPLICATIONS = './System/Applications/';
    const PATH_SYSTEM_RESOURCES = './System/Resource/';
    const PATH_SYSTEM_EXTERNALS = './System/External/';
    const PATH_SYSTEM_RESOURCE_STRINGS = './System/Resource/Strings/';

    const PATH_CONTENT = './Content/';
    const PATH_CONFIGURATION = './Content/configuration/';
    const PATH_TEMPLATES = './Content/CTemplate/';
    const PATH_LOGS = './Content/logs/';
    const PATH_DESIGN = './Content/stylesheets/';
    const PATH_SCRIPT = './Content/javascripts/';
    const PATH_TEMP = './Content/temp/';

	/**
	 * @var array
	 */
	private static $interfaceLookup = array();
	private static $GUIDLookup = null;
	private static $settings = null;
	private static $fs = null;
	
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

	protected static function pathPartForClass($class){
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
		return implode('/', $namespaceParts);
	}

	/**
	 * read data objects
	 * @param unknown_type $file
	 */
	public static function dataFromJSONFile($file){
		$data = self::dataFromFile($file);
		return $data ? @json_decode($data, true) : null;
	}

	/**
	 * read data objects
	 * @param unknown_type $file
	 */
	public static function dataFromFile($file, $compressed = false){
		$compressed = $compressed && extension_loaded('zlib');
		$data = null;
		if(file_exists($file) && is_readable($file)){
			if($compressed){
				$data = '';
				$fp = gzopen($file, 'r');
				while($blob = gzread($fp, 4096)){
					$data .= $blob;
				}
				gzclose($fp);
			}
			else{
				$data = file_get_contents($file);
			}
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

	public static function dataToFile($data, $file, $compress = false){
		$compress = $compress && extension_loaded('zlib');
		if(!is_dir(dirname($file))){
			throw new XFileNotFoundException('missing parent dir', $file, 0);
		}
		if(file_exists($file) && !is_writeable($file)){
			throw new XFileLockedException('file not writeable', $file, 1);
		}
		$t = tempnam(CMS_TEMP, 'Core_tmp_');
		if($compress){
			$fp = gzopen($t, 'w');
			gzwrite($fp, $data);
			gzclose($fp);
		}
		else{
			$fp = fopen($t, 'w');
			fwrite($fp, $data);
			fclose($fp);
		}
		if(!@rename($t, $file)){
			@unlink($t);
			throw new Exception('could not write file');
		}
	}

	/**
	 * @return Settings
	 */
	public static function Settings(){
		if(self::$settings == null){
			self::$settings = new Settings();
		}
		return self::$settings;
	}

	/**
	 * @return Interface_Database_QueryFactory
	 */
	public static function Database(){
		if(class_exists('DatabaseAdapter')){
			return DatabaseAdapter::getInstance();
		}
		return null;
	}

	/**
	 * @return Core_FileSystem
	 */
	public static function FileSystem(){
		if(!self::$fs){
			self::$fs = new Core_FileSystem(constant('CMS_ROOT'));
		}
		return self::$fs;
	}

			//lock instances
	protected function __construct(){}
	private function __clone(){}
}
?>