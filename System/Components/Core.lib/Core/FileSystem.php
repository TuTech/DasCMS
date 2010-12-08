<?php
class Core_FileSystem
{
	const DATA_HEADER = "<?php exit(); ?>\n";
	protected $opened = array();
	protected $dir;
	protected $rootDir;
	
	public function __construct($rootDir) {
		$this->rootDir = $rootDir;
	}

	protected function switchToRootDir(){
		$this->dir = getcwd();
		if($this->dir != $this->rootDir){
			chdir($this->rootDir);
		}
	}

	protected function restoreDir(){
		if($this->dir 
				&& $this->dir != $this->rootDir
				&& is_dir($this->dir)
		){
			chdir($this->dir);
		}
	}

	/**
	 * save string data to file (path relative from cms root)
	 * @param string $file
	 * @param string $data
	 * @return bool
	 */
	public function store($file, $data)
	{
		$this->switchToRootDir();
		try{
			$head = $this->suffix($file) == 'php' ? self::DATA_HEADER : '';
			$data = $head.$data;
			$ret = Core::dataToFile($data, $file, false);
			$this->restoreDir();
			return $ret;
		}
		catch (Exception $e){
			$this->restoreDir();
			throw $e;
		}
	}

	/**
	 * save any data type to file
	 * @param string $file
	 * @param mixed $data
	 * @return bool
	 */
	public function storeDataEncoded($file, $data){
		return $this->store($file, serialize($data));
	}

	/**
	 * read string data from file
	 * @param string $file
	 * @return string
	 */
	public function load($file){
		$this->switchToRootDir();
		try{
			$data = Core::dataFromFile($file);
			if($this->suffix($file) == 'php'){
				$pos = (strpos($data, "\n") === false) ? 0 : strpos($data, "\n")+1;
				$data = substr($data, $pos);
			}
			$this->restoreDir();
			return $data;
		}
		catch (Exception $e){
			$this->restoreDir();
			throw $e;
		}
	}

	public function loadEncodedData($file){
		SErrorAndExceptionHandler::muteErrors();
		$data = unserialize($this->load($file));
		SErrorAndExceptionHandler::reportErrors();
		return $data;
	}

	public function append($dataFile, $data)
    {
		$this->switchToRootDir();
		SErrorAndExceptionHandler::muteErrors();
        $fp = @fopen($dataFile,'a');
        if($fp)
        {
			fwrite($fp, $data);
			fclose($fp);
        }
		$this->restoreDir();
		SErrorAndExceptionHandler::reportErrors();
		if(!$fp){
			throw new XFileLockedException('open failed ',$dataFile);
		}
    }


	public function filesOf($dir, $match = false)
    {
		$this->switchToRootDir();
		$files = array();
        if(is_dir($dir) && chdir($dir))
        {
            $handle = openDir('.');
            while($item = readdir($handle))
            {
                if(!is_dir($item)
					&& substr($item,0,1) != '.'
                    && (!$match || preg_match($match, $item))
                ){
                    $files[] = $item;
                }
            }
            asort($files, SORT_LOCALE_STRING);
            closedir($handle);
        }
        else
        {
            throw new XFileNotFoundException('dir not found ',$dir,1);
        }
        chdir($this->rootDir);
		$this->restoreDir();
        return $files;
    }

	public function suffix($of){
		$tmp = explode('.',strtolower($of));
        return array_pop($tmp);
	}

	public function formatFileSize($bytes)
    {
        $units = array('B','KB','MB','GB','TB','PB','EB','ZB','YB');
        $loops = 0;
        while($bytes >= 1000)
        {
            $loops++;
            $bytes /= 1000;
        }
        return round($bytes,2).$units[$loops];
    }
}
?>
