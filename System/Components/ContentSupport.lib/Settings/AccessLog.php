<?php
/**
 * Description of ContentView
 *
 * @author lse
 */
class Settings_AccessLog extends BObject
	implements
        HRequestingClassSettingsEventHandler,
        HUpdateClassSettingsEventHandler
{
	public function HandleRequestingClassSettingsEvent(ERequestingClassSettingsEvent $e) {
		$e->addClassSettings($this, 'content_view', array(
        	'log_access' => array(Core::settings()->get('log_page_accesses'), Settings::TYPE_CHECKBOX, null, 'log_access')
		));
	}

	public function HandleUpdateClassSettingsEvent(EUpdateClassSettingsEvent $e) {
		$data = $e->getClassSettings($this);
		if(isset($data['log_access'])){
			Core::settings()->set('log_page_accesses', $data['log_access']);
		}
	}
}
?>