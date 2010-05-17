<?php
/**
 * Description of Settings
 *
 * @author lse
 */
class Settings extends Core {
    const TYPE_TEXT = 1;
    const TYPE_CHECKBOX = 2;
    const TYPE_SELECT = 3;
    const TYPE_PASSWORD = 4;

	protected $confFile, $prevConfFile;

	protected $updated = false;
	protected $data;

	protected function  __construct() {
		$this->prevConfFile = SPath::CONTENT.'configuration/system.prev.php';
		$this->confFile = SPath::CONTENT.'configuration/system.php';

		$file = $this->confFile;
		if(RURL::has('@previousconfig')
				&& file_exists($this->prevConfFile)
				&& PAuthorisation::has('org.bambuscms.login')
		)
		{
			//show with previous version
			$file = $this->prevConfFile;
		}
		$this->data = DFileSystem::LoadData($file);
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
        if($this->updated)
        {
            try{
                chdir(BAMBUS_CMS_ROOTDIR);
                SErrorAndExceptionHandler::muteErrors();
                //make a backup
                $oc = DFileSystem::Load($this->confFile);
                DFileSystem::Save($this->prevConfFile, $oc);

                //save new config
                DFileSystem::SaveData($this->confFile, $this->data);
                SNotificationCenter::report('message', 'configuration_saved');

                SErrorAndExceptionHandler::reportErrors();
            }
            catch(Exception $e)
            {
                SNotificationCenter::report('warning', 'configuration_not_saved');
            }
        }
    }
}
?>