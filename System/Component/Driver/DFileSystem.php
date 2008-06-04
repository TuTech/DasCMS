<?php
/**
 * @package Bambus
 * @subpackage Drivers
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 28.11.2007
 * @license GNU General Public License 3
 */
class DFileSystem extends BDriver
{
	const FHEADER = "<?php exit(); ?>\n";
	
	private static $_locked = array();
	
	/**
	 * Concurent access
	 *
	 * @param resource $fp
	 * @return bool
	 * @throws XFileLockedException
	 */
	private static function lock($fp)
	{
		return true;
//		if(!is_resource($fp))
//			return false;
//		$tries = 0;
//		while ($tries < 100 && !flock($fp, LOCK_EX))
//		{
//			$tries++;
//			usleep(rand(1,10));
//		}
//		if($tries < 100)
//		{
//			//flock($fp, LOCK_NB);	
//			array_unshift(self::$_locked, $fp);
//			self::$_locked = array_unique(self::$_locked);
//			return true;
//		}
//		throw new XFileLockedException('lock failed',$fp,0);
	}
	
	private static function unlock($fp)
	{
		if(is_resource($fp) && flock($fp, LOCK_UN))
		{
			self::$_locked = array_diff(self::$_locked, array($fp));
			return true;
		}
		return false;
	}
	
	/**
	 * Open a file specified by a path relative to the cms root dir
	 *
	 * @param string $file
	 * @param bool $write
	 * @return resource
	 * @throws XFileNotFoundException
	 * @throws XFileLockedException
	 */
	private static function open($file, $write = false)
	{
		chdir(constant('BAMBUS_CMS_ROOTDIR'));
		//open path and return fp
		//wait for locked 
		
		$openFile = null;
		if(!file_exists($file) && $write == false)
		{
			throw new XFileNotFoundException('open failed',$file);
		}
		
		
		//FIXME FOPEN etc works? (was r+)
		$openFile = @fopen($file,$write ? 'w+' : 'r+');
		if(!$openFile)
		{
			throw new XFileLockedException('open failed ',$file);
		}
		
		self::lock($openFile);
		return $openFile;
	}
	
	public static function close($fp)
	{
		if(is_resource($fp))
		{
			self::unlock($fp);
			return fclose($fp);
		}
		return false;
	}
	
	public function __destruct()
	{
		//unlock all
		foreach(self::$_locked as $fp)
		{
			self::unlock($fp);
		}
	}
	
	/**
	 * Updates serialized data in a file
	 *
	 * @param string $dataFile
	 * @param array $changes
	 * @throws XFileNotFoundException
	 * @throws XFileLockedException
	 */  
	public static function UpdateData($dataFile, $changes)
	{
		if(is_array($changes))
		{
			try
			{
				$data = self::LoadData($dataFile);
			}
			catch (Exception $e)
			{
				$data = array();
			}
			$fp = self::open($dataFile, true);
			foreach($changes as $key => $value)
			{
				if($value == null)
				{
					unset($data[$key]);	
				}
				else
				{
					$data[$key] = $value;
				}
			}
			$bin = serialize($data);
			fseek($fp, 0 , SEEK_SET);
			ftruncate($fp,0);
			$suc = fwrite($fp, self::FHEADER.$bin);
			self::close($fp);
			if(!$suc)
			{
				throw new XFileLockedException('data update failed ',$dataFile);
			}
		}
	} 
	
	/**
	 * Save any given data serialized to a file
	 *
	 * @param string $dataFile
	 * @param mixed $changes
	 * @return bool success
	 * @throws XFileNotFoundException
	 * @throws XFileLockedException
	 */	
	public static function SaveData($dataFile, $changes)
	{
		$suc = false;
		$fp = self::open($dataFile, true);
		$suc = @fwrite($fp, self::FHEADER.serialize($changes));
		self::close($fp);
		if(!$suc)
		{
			throw new XFileLockedException('save data failed ',$dataFile);
		}
		return $suc;
	} 

	/**
	 * Return unserialized content of a file
	 *
	 * @param string $dataFile
	 * @return mixed
	 * @throws XFileNotFoundException
	 * @throws XFileLockedException
	 * @throws XInvalidDataException
	 */
	public static function LoadData($dataFile)
	{
		$fp = null;
		if(!file_exists($dataFile))
		{
			throw new XFileNotFoundException('open failed',$dataFile);
		}
		
		
		//FIXME FOPEN etc works? (was r+)
		$fp = @fopen($dataFile,'r');
		if(!$fp)
		{
			throw new XFileLockedException('open failed ',$dataFile);
		}
		
		self::lock($fp);
		
		$bin = null;
		if(filesize($dataFile) > 0)
		{
			$bin = fread($fp, filesize($dataFile));
			$bin = substr($bin, strpos($bin, "\n")+1);
			$data = @unserialize($bin);
			if($data === false && $data == @unserialize(false))
			{
				throw new XInvalidDataException('failed to unserialize data from '.$dataFile);
			}
		}
		self::close($fp);
		return 	$data;
	} 
	
	/**
	 * Save raw data to a file
	 *
	 * @param string $dataFile
	 * @param mixed $changes
	 * @return bool
	 * @throws XFileNotFoundException
	 * @throws XFileLockedException
	 */
	public static function Save($dataFile, $changes)
	{
		$fp = self::open($dataFile, true);
		$suc = false;
		if($fp != null)
		{
			$suc = fwrite($fp, self::FHEADER.$changes);
		}
		self::close($fp);
		if(!$suc)
		{
			throw new XFileLockedException('save failed ',$dataFile);
		}
		return $suc;
	} 
	
	/**
	 * Read raw data from file
	 *
	 * @param string $dataFile
	 * @return string
	 * @throws XFileNotFoundException
	 * @throws XFileLockedException
	 */
	public static function Load($dataFile)
	{
		$bin = '';
		$fp = null;
		if(!file_exists($dataFile))
		{
			throw new XFileNotFoundException('open failed',$dataFile);
		}
		
		
		//FIXME FOPEN etc works? (was r+)
		$fp = @fopen($dataFile,'r');
		if(!$fp)
		{
			throw new XFileLockedException('open failed ',$dataFile);
		}
		
		self::lock($fp);
		if(filesize($dataFile) > 0)
		{
			$bin = fread($fp, filesize($dataFile));
			$pos = (strpos($bin, "\n") === false) ? 0 : strpos($bin, "\n")+1;
			$bin = substr($bin, $pos);
		}
		self::close($fp);
		return $bin;
	} 
	/**
	 * List files in $dir
	 * $match can be a regexp for file names
	 *
	 * @param string $dir
	 * @param mixed $match
	 * @return array
	 * @throws XFileNotFoundException
	 */
	public static function FilesOf($dir, $match = false)
	{
		chdir(constant('BAMBUS_CMS_ROOTDIR'));
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
					$files[strtoupper($item).md5($i)] = $item;
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
		chdir(constant('BAMBUS_CMS_ROOTDIR'));
		return $files;
	}
	
	/**
	 * Checks string being a valid file name
	 *
	 * @param string $name
	 * @return bool
	 */
	public static function ValidateName($name)
	{
		return preg_match("/^[\.-_a-zA-Z0-9]+$/", $name);
	}
	
	/**
	 * Deletes a file if allowed
	 *
	 * @param string $file
	 * @return bool
	 * @throws XFileLockedException
	 */
	public static function Delete($file)
	{
		if(
			file_exists($file)
			&&
			is_writable($file)
			&&
			@unlink($file)
		  )	
		{
		  	return true;
		}
		else
		{
		  	throw new XFileLockedException('delete failed', $file);
		}
	}
}

?>