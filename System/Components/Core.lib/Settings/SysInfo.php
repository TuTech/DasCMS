<?php
class Settings_SysInfo 
	implements
        Event_Handler_RequestingClassSettings,
        Event_Handler_UpdateClassSettings
{
	public function handleEventRequestingClassSettings(Event_RequestingClassSettings $e) {
		$e->addClassSettings($this, 'system_information', array(
			'php_version' => array(phpversion(), Settings::TYPE_INFORMATION, null, 'php_version'),
			'cache_size' => array($this->cacheSize(), Settings::TYPE_INFORMATION, null, 'cache_size'),
			'clear_cache' => array('', Settings::TYPE_CHECKBOX, false, 'clear_cache')
		));
	}

	public function handleEventUpdateClassSettings(Event_UpdateClassSettings $e) {
		$data = $e->getClassSettings($this);
		if(!empty($data['clear_cache'])){
			$this->clearCache();
		}
	}

	public function clearCache()
    {
		try{
			if(PAuthorisation::has('org.bambuscms.system.cache.clear'))
			{
				$myDir = getcwd();
				if(chdir(Core::PATH_TEMP))
				{
					$Dir = opendir ('./');
					while ($item = readdir ($Dir)) {
						if((is_file($item)) && (substr($item,0,1) != '.')){
							@unlink($item);
						}
					}
					closedir($Dir);
				}
				chdir(constant('BAMBUS_CMS_ROOTDIR'));
				SNotificationCenter::report('message', 'cache_cleared');
			}
		}
		catch (Exception $e){
			SNotificationCenter::report('warning', 'cache_not_cleared');
		}
    }

	public function cacheSize(){
		$myDir = getcwd();
		chdir(Core::PATH_TEMP);
		$Dir = opendir ('./');
		$size = 0;
		while ($item = readdir ($Dir)) {
			if(is_file($item)){
				$size += filesize($item);
			}
		}
		closedir($Dir);
		chdir(constant('BAMBUS_CMS_ROOTDIR'));
		return Core::FileSystem()->formatFileSize($size);
	}
}
?>