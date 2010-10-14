<?php
/**
 * Description of ContentView
 *
 * @author lse
 */
class Settings_AccessLog
	implements
        Event_Handler_RequestingClassSettings,
        Event_Handler_UpdateClassSettings
{
	public function handleEventRequestingClassSettings(Event_RequestingClassSettings $e) {
		$e->addClassSettings($this, 'content_handling', array(
        	'log_access' => array(Core::settings()->get('log_page_accesses'), Settings::TYPE_CHECKBOX, null, 'log_access')
		));
	}

	public function handleEventUpdateClassSettings(Event_UpdateClassSettings $e) {
		$data = $e->getClassSettings($this);
		if(isset($data['log_access'])){
			Core::settings()->set('log_page_accesses', $data['log_access']);
		}
	}
}
?>