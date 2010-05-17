<?php
/**
 * Description of ContentView
 *
 * @author lse
 */
class Settings_ContentView extends BObject
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
		$e->addClassSettings($this, 'content_view', array(
        	'default_view_for_relations' => array(Core::settings()->get('Settings_ContentView_relations'), Settings::TYPE_SELECT, $options, 'default_view_for_relations')
		));
	}
	
	public function HandleUpdateClassSettingsEvent(EUpdateClassSettingsEvent $e) {
		$data = $e->getClassSettings($this);
		$f = $data['default_view_for_relations'];
		$f = empty ($f) ? '' : $f;
		if(empty($f) || Formatter_Container::exists($f))
		{
			Core::settings()->set('Settings_ContentView_relations', $f);
		}
	}
}
?>