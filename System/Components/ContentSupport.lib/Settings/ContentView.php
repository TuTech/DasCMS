<?php
/**
 * Description of ContentView
 *
 * @author lse
 */
class Settings_ContentView extends BObject
	implements
        Event_Handler_RequestingClassSettings,
        Event_Handler_UpdateClassSettings
{
	public function handleEventRequestingClassSettings(Event_RequestingClassSettings $e) {
		$formatters = Formatter_Container::getFormatterList();
		$options = array(' - '.SLocalization::get('none').' - '  => '');
		foreach ($formatters as $f){
			$options[$f] = $f;
		}
		$e->addClassSettings($this, 'content_handling', array(
        	'default_view_for_content' => array(Core::settings()->get('Settings_ContentView_defaultContentView'), Settings::TYPE_SELECT, $options, 'default_view_for_content')
		));
	}
	
	public function handleEventUpdateClassSettings(Event_UpdateClassSettings $e) {
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