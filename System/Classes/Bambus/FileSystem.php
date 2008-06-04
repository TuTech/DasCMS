<?php 
/**
 * @package Bambus
 * @subpackage Deprecated 
 * @deprecated 
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 12.06.2006
 * @license GNU General Public License 3
 */
class FileSystem extends Bambus implements IShareable
{
	//IShareable
	const Class_Name = 'FileSystem';
	public static $sharedInstance = NULL;
	private static $initializedInstance = false;
	public static function alloc()
	{
		$class = self::Class_Name;
		if(self::$sharedInstance == NULL && $class != NULL)
		{
			self::$sharedInstance = new $class();
		}
		return self::$sharedInstance;
	}
    
    function init()
    {
    	if(!self::$initializedInstance)
    	{
    		if(defined('BAMBUS_DEBUG'))printf("\n<!--[%s init]-->\n", self::Class_Name);
	    	self::$initializedInstance = true;
			$this->NotificationCenter = NotificationCenter::alloc();

			$this->NotificationCenter->init();
    	}
    }
	//end IShareable

	var $dataHeader = "<?php /* Bambus Data File */ header(\"HTTP/1.0 404 Not Found\"); exit(); ?>\n";
	var $rootDir = '';
	var $cache = array();
	var $NotificationCenter;
	
	function __construct()
	{
		parent::Bambus();
	}	
	
    public function __sleep()
    {
    	//do not serialize
    	return array();
    }
    	
	function changeDir($newDir = '', $isPathAlias = true)
	{
		if($isPathAlias && !empty($newDir))
		{
			$newDir = parent::pathTo($newDir);
		}
		
		//no options -> root
		if(empty($newDir))
		{
			$newDir = BAMBUS_CMS_ROOT;
		}
		//try to open dir
		if(is_dir($newDir))
		{
			if(chdir($newDir))
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		//failed -> send alert
		else
		{
			$this->notifications[] =  'chdir_failed_for '.$newDir;
			return false;
		}
	}
	
	function returnToRootDir()
	{
		$this->changeDir(BAMBUS_CMS_ROOT);
	}
	
	function delete($file)
	{
		if(
			file_exists($file)
			&&
			is_writable($file)
		  )	
		{
		  	return @unlink($file);
		}
		else
		{
		  	return false;
		}
	}
	
 	///////////////////////////////////////////////////////////////
   	//get all files from $dir where type is (or is not) in $types//
   	///////////////////////////////////////////////////////////////
   	
	function getFiles($dir, $types, $useOrUseNot = true, $addFullPath = false)
	{
		/*
		<documentation>
			$useOrUseNot == true: list files whith suffix part of $types
			$useOrUseNot == false: list files whith suffix NOT part of $types
		</documentation>
		*/
		$types = parent::isAnArray($types);
		$this->changeDir($dir);
		$files = array();
		$handle = openDir('.');
		$i=0;
		while($item = readdir($handle))
		{
			while(is_link($item))
				$item = readlink($item);
			if(is_dir($item))continue;
			$i++;
			if(substr($item,0,1) != '.' && ((in_array($this->suffix($item), $types) && $useOrUseNot) || (!in_array($this->suffix($item), $types) && !$useOrUseNot)))
			{
				$files[strtoupper($item).md5($i)] = ($addFullPath) ? $this->pathTo($dir).$item : $item;
			}
		}
		asort($files, SORT_STRING);
		closedir($handle);
		$this->returnToRootDir();
		return $files;
	}
	
	function queryPath($path, $prefix, $addFullPath = false)
	{
		/*
		<documentation>
			$useOrUseNot == true: list files whith suffix part of $types
			$useOrUseNot == false: list files whith suffix NOT part of $types
		</documentation>
		*/
		$this->changeDir($path);
		$files = array();
		$handle = openDir('.');
		$i=0;
		while($item = readdir($handle))
		{
			while(is_link($item))
				$item = readlink($item);
			if(is_dir($item))
				continue;
			$i++;
			if(substr($item,0,strlen($prefix)) == $prefix)
			{
				$files[strtoupper($item).md5($i)] = ($addFullPath) ? $this->pathTo($path).$item : $item;
			}
		}
		asort($files, SORT_STRING);
		closedir($handle);
		$this->returnToRootDir();
		return $files;
	}
	
	/////////////////////////
	//get content of a file//
	/////////////////////////
	
    function read($file, $cached = false)
    {
    	if($cached && isset($this->cache[$file]))
    	{
    		return $this->cache[$file];
    	}
    	if(file_exists($file) && is_readable($file))
    	{
        	$handle = fopen ($file, "r");
        	if(filesize($file) > 0)
			{
				$contents = fread ($handle, filesize($file));
			}
			else
			{
				$contents = '';
			}
			fclose ($handle);
        	return $contents;
    	}
    	else
    	{
    		return '';
    	}
    }
	
    function readLine($file, $line)
    {
    	if(file_exists($file) && is_readable($file) && is_numeric($line))
    	{
			$lines = file($file);
			if(isset($lines[$line]))
			{
	        	return $lines[$line];
			}
    	}
		return '';
    }
        
    ///////////////////////////
    //advanced write function//
    ///////////////////////////
    /*$tmpfname = tempnam("/tmp", "FOO");

$handle = fopen($tmpfname, "w");
fwrite($handle, "writing to tempfile");
fclose($handle);

// do here something

unlink($tmpfname);*/
    function write($file, $data = '', $mode = 'w+', $rights = 0666, $cached = true)
    {
    	$success = false;
    	if(
			(file_exists($file) && is_writable($file)) 
			|| 
			(!file_exists($file) && is_writable(dirname($file)))
    	)
    	{
    		if(file_exists($file)){
	    		$tried = 0;
	    		while(!is_writable($file) && $tried < 255)
	    		{
	    			//might by locked - retry
	    			$tried++;
	    			usleep(rand(1,100));
	    		}
				if($tried == 255)
				{
					//could not open file
					$this->NotificationCenter->report('alert', 'file_write_locked', array('file' => realpath($file)));
					return false; 
				}
    		}
        	try
        	{
				$openFile = fopen($file,$mode);
	        	flock($openFile, LOCK_EX + LOCK_NB);
            	$success = fwrite($openFile, $data);
        	}
        	catch(Exception $e)
        	{
    	    	flock($openFile, LOCK_UN);
        	}
        	if($success && $cached)
        	{
        		$this->cache[$file] = $data;
        	}
        	elseif(!$success && $cached)
        	{
        		unset($this->cache);
        	}
	    	flock($openFile, LOCK_UN);
            fclose($openFile);
            @chmod($file, $rights);
    	}
    	else
    	{
    		$this->NotificationCenter->report('alert', 'file_or_directory_locked', array('file' => getcwd().'/'.($file)));
    	}
    	return $success;
    }
    
    function writeLine($file, $line = '')
    {
    	return $this->write($file, $line."\n", 'a+');
    }
    
    /////////////
    //read data//
    /////////////

	function readData($file, $cached = false)
	{
		if(file_exists($file) && is_readable($file))
    	{
    		if($cached && isset($this->cache[$file]))
    		{
    			$data = $this->cache[$file];
    		}
    		else
	    	{	
	    		$data = file($file);
	    	}
    		if(count($data) >= 2 /* && trim($data[0]) == trim($this->dataHeader)*/)
    		{
    			unset($data[0]);
    			$dataString = implode('',$data);
    			$data = unserialize($dataString);
    			if($data === false)
    			{
    				return -1;
    			}
	        	return $data;
    		}
    	}
    	return false;
	}    
	
	//////////////
	//write data//
	//////////////
	
	function writeData($file, $data, $cached = true)
	{
		$data = $this->dataHeader.serialize($data);
		return $this->write($file, $data, 'w+', 0666, $cached);
	}
}
?>