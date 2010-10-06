<?php
/**
 * Description of Settings
 *
 * @author lse
 */
class Settings_HTMLCleaner
	extends BObject
    implements
        Event_Handler_UpdateClassSettings,
        Event_Handler_RequestingClassSettings
{
    public function handleEventRequestingClassSettings(Event_RequestingClassSettings $e)
    {
        //db_engine + whatever DSQL gives us
        $e->addClassSettings($this, 'content_handling', array(
        	'remove_javascript_on_save' => array(Core::settings()->get('HTMLCleaner_Remove_Scripts'), Settings::TYPE_CHECKBOX, null, 'remove_javascript_on_save'),
        	'remove_style_attributes_on_save' => array(Core::settings()->get('HTMLCleaner_Remove_StyleAttribute'), Settings::TYPE_CHECKBOX, null, 'remove_style_attributes_on_save')
        ));
    }

    public function handleEventUpdateClassSettings(Event_UpdateClassSettings $e)
    {
        $data = $e->getClassSettings($this);
		$active = false;
		if(isset($data['remove_javascript_on_save']))
		{
			$jsactive = !empty ($data['remove_javascript_on_save']);
			$active = $active || $jsactive;
			Core::settings()->set('HTMLCleaner_Remove_Scripts', $jsactive ? '1' : '');
		}
		if(isset($data['remove_style_attributes_on_save']))
		{
			$stactive = !empty ($data['remove_style_attributes_on_save']);
			$active = $active || $stactive;
			Core::settings()->set('HTMLCleaner_Remove_StyleAttribute', $stactive ? '1' : '');
		}
		Core::settings()->set('HTMLCleaner_Clean_HTML', $active ? '1' : '');
    }
}
?>