<?php
class post_setup_SComponentIndex implements runnable 
{
	private function write($file,$data)
	{
		if(!($fp = fopen($file, 'w+')) || !fwrite($fp, $data))
		{
			throw new Exception('cound not write to file '. $file);
		}
		fclose($fp);
	}
	
	private function throwNotEmpty($err = null)
	{
		if(!empty($err))
		{
			throw new Exception($err);
		}
	}
	
	private function reportSuccess($target, $type, $successful)
	{
		printf(
			"<li>Creating %s %s...%s</li>",
			$type,
			$target,
			($successful) ? '<span style="color:#4e9a06">OK</span>' : '<span style="color:red">FAILED</span>'
		);
	}
	const EXTENSIONS = 0;
	const INTERFACES = 1;
	
	private static $_classIndex = array();
	private static $_interfaceIndex = array();
	private static	$_components = array(
		'A' => 'AppController',
		'B' => 'Base',
		'C' => 'Content',
		'D' => 'Driver',
		'E' => 'Event',
		'H' => 'EventHandler',
		'I' => 'Interface',
        'L' => 'Legacy',
		'M' => 'Manager',
		'N' => 'Navigator',
		'P' => 'Provider',
		'Q' => 'Query',
		'R' => 'Request',
        'S' => 'System',
		'T' => 'TemplateEngine',
        'V' => 'View',
        'W' => 'Widget',
		'X' => 'Exception'
		);
	
	/**
	 * List files in $dir
	 * $match can be a regexp for file names
	 *
	 * @param string $dir
	 * @param mixed $match
	 * @return array
	 * @throws XFileNotFoundException
	 */
	private static function FilesOf($dir, $match = false)
	{
		$cdir = getcwd();
		//chdir(constant('CMS_ROOTDIR'));
		$files = array();
		if(is_dir($dir) && chdir($dir))
		{
			$handle = openDir('.');
			$i=1;
			while($item = readdir($handle))
			{
				if(is_dir($item))
				{
					continue;
				}
				if(substr($item,0,1) != '.' 
					&& (!$match || preg_match($match, $item))
				)
				{
					$files[strtoupper($item).$i] = $item;
				}
				$i++;
			}
			asort($files, SORT_LOCALE_STRING);
			closedir($handle);
		}
		else
		{
			throw new XFileNotFoundException('dir not found ',$dir,1);
		}
		chdir($cdir);
		return $files;
	}
	
	/**
	 * Build index of all component classes
	 * 
	 * @param bool $verbose generate list of indexed components
	 */
	private function Index()
	{
		$verbose = false;
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
			if($verbose)printf("<li>Component '%s'</li>\n", $var);
			$comp = self::FilesOf('System/Component/'.$var.'/');
			foreach ($comp as $c) 
			{
				if($verbose)print('<ul>');
				try
				{
					include_once('./System/Component/'.$var.'/'.$c);
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
				catch(Exception $e)
				{
					//ignore the misfits!
				}
				if($verbose)print('</ul>');
			}
		}
		$ci = "<?php exit(); ?".">\n".serialize(self::$_classIndex);
		$this->write('Content/SComponentIndex/classes.php', $ci);
		
		$ii = "<?php exit(); ?".">\n".serialize(self::$_interfaceIndex);
		$this->write('Content/SComponentIndex/interfaces.php', $ii);
		
		
		$managers = array();
		foreach(array_keys(self::$_classIndex) as $class)
		{
			if(isset(self::$_classIndex[$class][self::EXTENSIONS]["BContentManager"]))
			{
				$managers[] = $class;
			}
		}
		$prefix = SetupConfiguration::get('db_table_prefix');
		$mangs = array();
		switch(SetupConfiguration::get('db_engine'))
		{
			case 'SQLite':
			$DB = new SQLiteDatabase("Content/DSQL_SQLite/bambus.sqlite", 0600, $err);
			foreach ($managers as $mngr) 
			{
				$sql =  "INSERT OR IGNORE INTO {$prefix}Managers (manager) VALUES ('".sqlite_escape_string($mngr)."');";
				$this->reportSuccess($mngr, 'Entry', $DB->queryExec($sql, $err));
			}
			break;
			case 'MySQL':
			
			$DB = new mysqli(
				$this->getCfgOrDefault('db_server'),
				$this->getCfgOrDefault('db_user'),
				$this->getCfgOrDefault('db_password'),
				$this->getCfgOrDefault('db_name'),
				$this->getCfgOrDefault('db_port'),
				$this->getCfgOrDefault('db_socket'));
				//$srv,$usr,$pw,$db,$prt,$sck);
			foreach ($managers as $mngr) 
			{
				$sql =  "INSERT IGNORE INTO {$prefix}Managers (manager) VALUES ('".$DB->real_escape_string($mngr)."');";
				$this->reportSuccess($mngr, 'Entry', $DB->query($sql));
			}
			break;
			default:throw new Exception('unknown db engine '.SetupConfiguration::get('db_engine'));
		}
		foreach(array_keys(self::$_classIndex) as $class)
		{
			if(isset(self::$_classIndex[$class][self::INTERFACES]['IUseSQLite']))
			{
				printf('<li>DB Class: %s</li>', $class);
			}
		}
	}
	
	private function getCfgOrDefault($key, $else = null)
	{
		$dat = SetupConfiguration::get($key);
		if(empty($dat))
		{
			$dat = $else;
		}
		return $dat; 
	}
	
	public function run()
	{
		if(!is_dir('Content/SComponentIndex/'))
		{
			mkdir('Content/SComponentIndex/');
		}
		$cdir = getcwd();
		$this->Index();
		chdir($cdir);
	}
}
?>