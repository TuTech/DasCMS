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
        	'default_view_for_content' => array(Core::settings()->get('Settings_ContentView_defaultContentView'), Settings::TYPE_SELECT, $options, 'default_view_for_content')
		));
	}
	
	public function HandleUpdateClassSettingsEvent(EUpdateClassSettingsEvent $e) {
		$data = $e->getClassSettings($this);
		if(isset($data['default_view_for_content'])){
			$f = $data['default_view_for_content'];
			$f = empty ($f) ? '' : $f;
			if(empty($f) || Formatter_Container::exists($f))
			{
				Core::settings()->set('Settings_ContentView_defaultContentView', $f);
			}
		}
	}
}
?>