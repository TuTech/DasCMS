<?php
/**
 * Description of ContentView
 *
 * @author lse
 */
class Settings_ContentRelationsView extends BObject
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
		$e->addClassSettings($this, 'content_handling', array(
			'default_view_for_relations' => array(Core::settings()->get('Settings_ContentRelationsView_relations'), Settings::TYPE_SELECT, $options, 'default_view_for_relations')
		));
	}
	
	public function HandleUpdateClassSettingsEvent(EUpdateClassSettingsEvent $e) {
		$data = $e->getClassSettings($this);
		if(isset($data['default_view_for_relations'])){
			$f = $data['default_view_for_relations'];
			$f = empty ($f) ? '' : $f;
			if(empty($f) || Formatter_Container::exists($f))
			{
				Core::settings()->set('Settings_ContentRelationsView_relations', $f);
			}
		}
	}
}
?>