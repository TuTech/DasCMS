<?php
/**
 * Description of ContentView
 *
 * @author lse
 */
class Settings_ContentView 
	implements
        Event_Handler_RequestingClassSettings,
        Event_Handler_UpdateClassSettings
{
	public function handleEventRequestingClassSettings(Event_RequestingClassSettings $e) {
		$formatters = Controller_View::getInstance()->getStoredViews();
		$options = array(' - '.SLocalization::get('none').' - '  => '');
		foreach ($formatters as $f){
			$options[$f] = $f;
		}
		$e->addClassSettings($this, 'content_handling', array(
        	'default_view_for_content' => array(Core::Settings()->get('Settings_ContentView_defaultContentView'), Settings::TYPE_SELECT, $options, 'default_view_for_content')
		));
	}
	
	public function handleEventUpdateClassSettings(Event_UpdateClassSettings $e) {
		$data = $e->getClassSettings($this);
		if(isset($data['default_view_for_content'])){
			$f = $data['default_view_for_content'];
			$f = empty ($f) ? '' : $f;
			if(empty($f) || Controller_View::getInstance()->hasView($f))
			{
				Core::Settings()->set('Settings_ContentView_defaultContentView', $f);
			}
		}
	}
}
?>