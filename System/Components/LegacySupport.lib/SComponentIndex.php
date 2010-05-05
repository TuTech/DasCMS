<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2008-04-28
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage System
 */
class SComponentIndex 
    extends 
        BObject 
    implements 
        IShareable 
{
	private static	$_components = array(
		'A' => 'AppController',
		'B' => 'Base',
		'C' => 'Content',
		'D' => 'Driver',
		'E' => 'Event',
		'H' => 'EventHandler',
		'I' => 'Interface',
		'J' => 'Job',
        'L' => 'Legacy',
		'N' => 'Navigator',
		'P' => 'Provider',
		'Q' => 'Query',
		'R' => 'Request',
        'S' => 'System',
		'T' => 'TemplateEngine',
		'U' => 'Plugin',
        'V' => 'View',
        'W' => 'Widget',
		'X' => 'Exception'
		);
	
	const EXTENSIONS = 0;
	const INTERFACES = 1;
	
	private static $_classIndex = array();
	private static $_interfaceIndex = array();
	
	/**
	 * Get the path for a component class
	 *
	 * @param string $class
	 * @return string path
	 */
	public static function ComponentPath($class)
	{
		$fc = substr($class,0,1); //first char
		$sc = substr($class,1,1); //second char
		return sprintf("./System/Component/%s/%s.php", $Components[$fc], $class);
	}
	
	private function getObjectFiles($dir, &$files)
	{
	    $items = scandir($dir);
	    $dir = rtrim($dir, '/');
	    $dirName = ltrim($dir, './');
	    foreach ($items as $item)
	    {
	        if(is_file($dir.'/'.$item))
	        {
	            echo 'adding file: '.$dirName.'_'.$item.'<br />';
	            if(substr($item,0,1) == '_')
	            {
	                $fileName = str_replace('/', '_', '_'.$dirName).'junk';
	            }
	            else
	            {
	                $fileName = str_replace('/', '_', $dirName.'_'.$item);
	            }
	            echo 'as class: <b>'.$fileName.'</b><br />';
	            $files[] = $fileName;
	        }
	        elseif(is_dir($dir.'/'.$item) && substr($item,0,1) != '.')
	        {
	            echo 'searching directory: '.$dirName.'_'.$item.'<br />';
	            $this->getObjectFiles($dir.'/'.$item, $files);
	        }
	    }
	}
	
	private function doClasses(array $comp, $verbose, &$db_class_index)
	{
	    $err = 0;
		$errarr = array();
		foreach ($comp as $c) 
		{
		    if($verbose)print('<li>');
			if($verbose)print('<ul>');
			try
			{
				$c = substr($c,0,-4);
				if(interface_exists($c, true))
				{
					if($verbose)printf("Interface '<i>%s</i>'<br />", $c);
					self::$_interfaceIndex[$c] = 1;
				}
				elseif(class_exists($c, true))
				{
					self::$_classIndex[$c] = array(self::INTERFACES => array(), self::EXTENSIONS => array());
					if($verbose)printf("Class '<b>%s</b>'<ul><u>implements:</u><ol>", $c);
					$impl = class_implements($c);
					foreach ($impl as $itf) 
					{
						if($verbose)printf("<li>%s</li>", $itf);
						self::$_classIndex[$c][self::INTERFACES][$itf] = 1;
					}
					////
					if($verbose)print("</ol><u>extends:</u><ol>");
					$ext = class_parents($c);
					foreach ($ext as $par) 
					{
						if($verbose)printf("<li>%s </li>", $par);
						self::$_classIndex[$c][self::EXTENSIONS][$par] = 1;
					}
					////
					if($verbose)print("</ol><u>Functions:</u><ol>");
					$impl = get_class_methods($c);
					foreach ($impl as $itf) 
					{
						if($verbose)printf("<li>%s</li>", $itf);
					}
					////
					if($verbose)print("</ol><u>Static vars:</u><ol>");
					$impl = get_class_vars($c);
					foreach ($impl as $itf => $bla) 
					{
						if($verbose)printf("<li>%s</li>", $itf);
					}
					if($verbose)print("</ol></ul>");
					////DB stuff
					$guid = '';
					if(isset(self::$_classIndex[$c][self::INTERFACES]['IGlobalUniqueId']))
					{
					    $guid = constant($c.'::GUID');
					    if($verbose)printf("<p><b>%s</b></p>", $guid);
					}
					$db_class_index[$c] = $guid;
				}
				else
				{
					if($verbose)printf("Undefined '<s>%s</s>'<br />", $c);
				}
			}
			catch(Exception $e)
			{
				//ignore the misfits!
			}
			if($verbose)print('</ul>');
			if($verbose)print('</li>');
		}
		if($verbose && $err > 0)
		{
			echo '<div style="display:block;font-family:sans-serif; border:1px solid red; background: #a40000; color:white; position:fixed;right:5px;top:5px;padding:5px;">';
			echo '<b>Errors:</b><br />';
			foreach ($errarr as $e => $file) 
			{
				echo '<a href="#BADF00D',$e,'" style="color:white;">', $file, '</a><br />';
			}
			echo '</div>';
			
		}
	}
	
	/**
	 * Build index of all component classes
	 * 
	 * @param bool $verbose generate list of indexed components
	 */
	public function Index($verbose = true)
	{
		//in Content/SComponentIndex/
		//build interface db
		//build class db
		//build structure.html
		//allow doing this from cfg app
					
		//object loader
		
		$cdir = getcwd();
		chdir('System/Object');
		$files = array();
		try{
		$this->getObjectFiles('.',$files);
		}catch (Exception $e)
		{
		    echo $e;
		}
		chdir($cdir);
		
		//end object loader
		
		$db_class_index = array();

		$err = 0;
		$errarr = array();
		self::$_interfaceIndex = array();
		self::$_classIndex = array();
		if($verbose)print('<ol>');
		foreach (self::$_components as $prefix => $var) 
		{
			if($verbose)printf("<h3>Component '%s'</h3>\n", $var);
			$comp = DFileSystem::FilesOf('System/Component/'.$var.'/');
			$this->doClasses($comp, $verbose, $db_class_index);
		}
		$this->doClasses($files, $verbose, $db_class_index);
		if($verbose)print('</ol>');
		DFileSystem::SaveData($this->StoragePath('classes'), self::$_classIndex);
		DFileSystem::SaveData($this->StoragePath('interfaces'), self::$_interfaceIndex);
		$dbEngine = LConfiguration::get('db_engine');
		
		QSComponentIndex::updateClassIndex($db_class_index);
	}

	/**
	 * Find the direct parent of $class
	 *
	 * @param string|object $class
	 * @return string|null
	 * @throws XUndefinedIndexException
	 */
	public function ParentOf($class)
	{
		$className = (is_object($class)) ? get_class($class) : $class;
		if(!isset(self::$_classIndex[$className]))
		{
			throw new XUndefinedIndexException('class not indexed');
		}
		$ancestors = array_keys(self::$_classIndex[$className][self::EXTENSIONS]);
		return isset($ancestors[0]) ? $ancestors[0] : null;
	}
	
	/**
	 * Find all classes this class is based on 
	 *
	 * @param string|object $class
	 * @return array numeric array sorted from parent to root class
	 * @throws XUndefinedIndexException
	 */
	public function AncestorsOf($class)
	{
		$className = (is_object($class)) ? get_class($class) : $class;
		if(!isset(self::$_classIndex[$className]))
		{
			throw new XUndefinedIndexException('class not indexed');
		}
		return array_keys(self::$_classIndex[$className][self::EXTENSIONS]);
	}
	
	/**
	 * Find all classes implementing the given interface
	 *
	 * @param string $interface
	 * @return array
	 * @throws XUndefinedIndexException
	 */
	public function ImplementationsOf($interface)
	{
		//go through all classes and their INTERFACES array
		if(!isset(self::$_interfaceIndex[$interface]))
		{
			throw new XUndefinedIndexException('interface not indexed');
		}
		$res = array();
		foreach(array_keys(self::$_classIndex) as $class)
		{
			if(isset(self::$_classIndex[$class][self::INTERFACES][$interface]))
			{
				$res[] = $class;
			}
		}
		return $res;
	}
	
	/**
	 * Is the $class an implementation of the $interface
	 *
	 * @param string|object $class
	 * @param string $interface
	 * @return bool
	 * @throws XUndefinedIndexException
	 */
	public function IsImplementation($class, $interface)
	{
		$className = (is_object($class)) ? get_class($class) : $class;
		if(!isset(self::$_interfaceIndex[$interface]))
		{
			throw new XUndefinedIndexException('interface not indexed');
		}
		if(!isset(self::$_classIndex[$className]))
		{
			throw new XUndefinedIndexException('class not indexed');
		}
		return isset(self::$_classIndex[$className][self::INTERFACES][$interface]);
	}
	
	/**
	 * Find all classes directly based on $class 
	 *
	 * @param string|object $class
	 * @return array
	 * @throws XUndefinedIndexException
	 */
	public function ExtensionsOf($class)
	{
		$className = (is_object($class)) ? get_class($class) : $class;
		if(!isset(self::$_classIndex[$className]))
		{
			throw new XUndefinedIndexException('class not indexed');
		}
		$res = array();
		foreach(array_keys(self::$_classIndex) as $class)
		{
			if(isset(self::$_classIndex[$class][self::EXTENSIONS][$className]))
			{
				$res[] = $class;
			}
		}
		return $res;
	}
	
	/**
	 * Is the given class somewhat derived from the base class
	 *
	 * @param string|object $class
	 * @param string|object $baseClass
	 * @return bool
	 * @throws XUndefinedIndexException
	 */
	public function IsExtension($class, $baseClass)
	{
		$className = (is_object($class)) ? get_class($class) : $class;
		$baseName = (is_object($baseClass)) ? get_class($baseClass) : $baseClass;
		if(!isset(self::$_classIndex[$className])
			|| !isset(self::$_classIndex[$baseName]))
		{
			throw new XUndefinedIndexException('class not indexed: '.$className);
		}
		return isset(self::$_classIndex[$className][self::EXTENSIONS][$baseName]);
	}
	
	//IShareable
	private static $initDone = false;
	const CLASS_NAME = 'SComponentIndex';
	public static $sharedInstance = NULL;
	private static $initializedInstance = false;
	
	/**
	 * allocate instance
	 *
	 * @return SComponentIndex
	 */
	public static function getSharedInstance()
	{
		$class = self::CLASS_NAME;
		if(self::$sharedInstance == NULL && $class != NULL)
		{
		    self::$sharedInstance = new $class();
		    try
    		{
    			self::$_classIndex = DFileSystem::LoadData(self::$sharedInstance->StoragePath('classes'));
    			self::$_interfaceIndex = DFileSystem::LoadData(self::$sharedInstance->StoragePath('interfaces'));    		
    		}
    		catch(XFileNotFoundException $e)
    		{
    			$this->Index(false);
    		}
    		catch (Exception $e)
    		{
    			echo $e.'<br />';
    		}
		}
		return self::$sharedInstance;
	}
	//end IShareable
	
}
?>