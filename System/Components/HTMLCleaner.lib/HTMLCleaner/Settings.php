<?php
/**
 * Description of Settings
 *
 * @author lse
 */
class HTMLCleaner_Settings
	extends BObject
    implements
        HUpdateClassSettingsEventHandler,
        HRequestingClassSettingsEventHandler
{
    public function HandleRequestingClassSettingsEvent(ERequestingClassSettingsEvent $e)
    {
        //db_engine + whatever DSQL gives us
        $e->addClassSettings($this, 'clean_content', array(
        	'remove_javascript_on_save' => array(Core::settings()->get('HTMLCleaner_Remove_Scripts'), Settings::TYPE_CHECKBOX, null, 'remove_javascript_on_save')
        ));
    }

    public function HandleUpdateClassSettingsEvent(EUpdateClassSettingsEvent $e)
    {
        $data = $e->getClassSettings($this);
		if(isset($data['remove_javascript_on_save']))
		{
			$active = empty ($data['remove_javascript_on_save']) ? '' : '1';
			Core::settings()->set('HTMLCleaner_Clean_HTML', $active);
			Core::settings()->set('HTMLCleaner_Remove_Scripts', $active);
		}
    }
}
?>