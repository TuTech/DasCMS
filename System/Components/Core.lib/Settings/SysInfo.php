<?php
class Settings_SysInfo extends BObject
	implements
        HRequestingClassSettingsEventHandler,
        HUpdateClassSettingsEventHandler
{
	public function HandleRequestingClassSettingsEvent(ERequestingClassSettingsEvent $e) {
		$formatters = Formatter_Container::getFormatterList();
		$options = array(' - '.SLocalization::get('none').' - '  => '');
		foreach ($formatters as $f){
			$options[$f] = $f;
		}
		$e->addClassSettings($this, 'system_information', array(
			'php_version' => array(phpversion(), Settings::TYPE_INFORMATION, null, 'php_version'),
			'cache_size' => array($this->cacheSize(), Settings::TYPE_INFORMATION, null, 'cache_size'),
			'clear_cache' => array('', Settings::TYPE_CHECKBOX, false, 'clear_cache')
		));
	}

	public function HandleUpdateClassSettingsEvent(EUpdateClassSettingsEvent $e) {
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
				if(chdir(SPath::TEMP))
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
		chdir(SPath::TEMP);
		$Dir = opendir ('./');
		$size = 0;
		while ($item = readdir ($Dir)) {
			if(is_file($item)){
				$size += filesize($item);
			}
		}
		closedir($Dir);
		chdir(constant('BAMBUS_CMS_ROOTDIR'));
		return DFileSystem::formatSize($size);
	}
}
?>