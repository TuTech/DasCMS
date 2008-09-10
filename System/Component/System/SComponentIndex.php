<?php
/**
 * @package Bambus
 * @subpackage System
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 28.04.2008
 * @license GNU General Public License 3
 */
class SComponentIndex extends BSystem implements IShareable 
{
	private static	$_components = array(
//		'A' => 'AppController',
		'B' => 'Base',
		'C' => 'Content',
		'D' => 'Driver',
		'E' => 'Event',
		'H' => 'EventHandler',
		'I' => 'Interface',
        'L' => 'Legacy',
		'M' => 'Manager',
		'N' => 'Navigator',
		'Q' => 'Query',
		'R' => 'Request',
        'S' => 'System',
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
		if($sc == strtolower($sc))//valid class name begins with to uppercase chars 
		{						  //use the content class as default
			$fc = 'M';
			$class = 'M'.$class;
		}
		return sprintf("./System/Component/%s/%s.php", $Components[$fc], $class);
	}
//@todo FIXME dump managers in db  
//foreach ($managers as $manager) 
//{
//	$result = $DB->queryExec("INSERT OR IGNORE INTO Managers (manager) VALUES ('".$manager."');");
//}
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
		$err = 0;
		$errarr = array();
		self::$_interfaceIndex = array();
		self::$_classIndex = array();
		foreach (self::$_components as $prefix => $var) 
		{
			if($verbose)printf("<h3>Component '%s'</h3>\n", $var);
			$comp = DFileSystem::FilesOf('System/Component/'.$var.'/');
			foreach ($comp as $c) 
			{
				if($verbose)print('<ul>');
				try
				{
//					ob_start();
//					echo '<div style="display:block; border:1px solid red;padding:5px;">';
//					$sys = system('php --syntax-check System/Component/'.$var.'/'.$c, $OKis0);
//					echo '</div>';
//					$ob = ob_get_contents();
//					ob_end_clean();
					if(true)//$OKis0 == 0)
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
						}
						else
						{
							if($verbose)printf("Undefined '<s>%s</s>'<br />", $c);
						}
					}
					else 
					{
						if($verbose)
						{
							echo '<a name="BADF00D'.$err.'"></a><strong style="color:red">System/Component/'.$var.'/'.$c.' not indexed!<br />Here is why:</strong>';
							echo $ob;
							echo var_dump($OKis0);
						}
						$errarr[$err] = $var.'/'.$c;
						$err++;
					}
					
				}
				catch(Exception $e)
				{
					//ignore the misfits!
				}
				if($verbose)print('</ul>');
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
		DFileSystem::SaveData($this->StoragePath('classes'), self::$_classIndex);
		DFileSystem::SaveData($this->StoragePath('interfaces'), self::$_interfaceIndex);
		$dbEngine = LConfiguration::get('db_engine');
		
		$managers = $this->ExtensionsOf("BContentManager");
		$mangs = array();
		foreach ($managers as $mngr) 
		{
			$mangs[] = array($mngr);
		}

		$DB = DSQL::alloc()->init();
		$DB->insert('Managers',array('manager'),$mangs);
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
	public static function alloc()
	{
		$class = self::CLASS_NAME;
		if(self::$sharedInstance == NULL && $class != NULL)
			self::$sharedInstance = new $class();
		return self::$sharedInstance;
	}
    
	/**
	 * Init instance
	 *
	 * @return SComponentIndex
	 */
    function init()
    {
    	if(!self::$initDone)
    	{
    		try
    		{
    			self::$_classIndex = DFileSystem::LoadData($this->StoragePath('classes'));
    			self::$_interfaceIndex = DFileSystem::LoadData($this->StoragePath('interfaces'));    		
    		}
    		catch(XFileNotFoundException $e)
    		{
    			$this->Index(false);
    		}
    		catch (Exception $e)
    		{
    			echo $e->getMessage().'<br />';
    		}
    	}
    	return $this;
    }
	//end IShareable
	
}
?>