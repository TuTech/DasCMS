<?php
/**
 * Description of Settings
 *
 * @author lse
 */
class Settings extends Core {
    const TYPE_INFORMATION = 0;
    const TYPE_TEXT = 1;
    const TYPE_CHECKBOX = 2;
    const TYPE_SELECT = 3;
    const TYPE_PASSWORD = 4;

	const FILE_CONFIG = 'Content/configuration/system.php';
	const FILE_CONFIG_BACKUP = 'Content/configuration/system.prev.php';

	const HEADER = "<?php exit(); /* ENCODING=JSON */ ?>\n";

	protected $confFile, $prevConfFile;

	protected $allowOverwrite = true;
	protected $updated = false;
	protected $data;

	protected function  __construct() {
		$file = self::FILE_CONFIG;
		if(Core::classExists('RURL')
				&& Core::classExists('PAuthorisation')
				&& RURL::has('@previousconfig')
				&& file_exists(self::FILE_CONFIG_BACKUP)
				&& PAuthorisation::has('org.bambuscms.login')
		)
		{
			//show with previous version
			$file = self::FILE_CONFIG_BACKUP;
		}
		if(file_exists($file)){
			$lines = file($file);
			if(is_array($lines)){
				$header = array_shift($lines);//remove header
				$data = implode('', $lines);
				if(strpos($header, 'ENCODING=JSON')!== false){
					//new way
					$this->data = json_decode($data, true);
				}
				else{
					//old way
					$this->data = unserialize($data);
				}
			}
		}
		else{
			$this->allowOverwrite = false;
			$this->data = array();
		}
	}

	public function get($key){
		return $this->getOrDefault($key, '');
	}

	public function getOrDefault($key, $default){
		if(array_key_exists($key, $this->data)){
			return $this->data[$key];
		}
		return $default;
	}

	public function set($key, $value){
		if($this->get($key) != $value){
			$this->data[$key] = $value;
			$this->updated = true;
		}
	}

	public function toArray(){
		return $this->data;
	}

	public function __destruct()
    {
		$hasEnv = Core::classExists('SNotificationCenter') && Core::classExists('SErrorAndExceptionHandler');

        if($this->updated && ($this->allowOverwrite || !file_exists(self::FILE_CONFIG)))
        {
            try{
                chdir(BAMBUS_CMS_ROOTDIR);
                if($hasEnv){
					SErrorAndExceptionHandler::muteErrors();
				}

				//make a backup
				if(file_exists(self::FILE_CONFIG)){
					copy(self::FILE_CONFIG, self::FILE_CONFIG_BACKUP);
				}

                //save new config
				$data = self::HEADER.json_encode($this->data);
				Core::dataToFile($data, self::FILE_CONFIG);

				if($hasEnv){
					SNotificationCenter::report('message', 'configuration_saved');
					SErrorAndExceptionHandler::reportErrors();
				}
            }
            catch(Exception $e)
            {
                if($hasEnv){
					SNotificationCenter::report('warning', 'configuration_not_saved');
				}
				else{
					echo 'WARNING: configuration not saved';
				}
            }
        }
    }
}
?>