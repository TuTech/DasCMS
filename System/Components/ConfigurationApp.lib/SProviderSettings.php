<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2009-05-13
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage Drivers
 */
class SProviderSettings 
    implements
        Event_Handler_UpdateClassSettings,
        Event_Handler_RequestingClassSettings
{
    public function handleEventRequestingClassSettings(Event_RequestingClassSettings $e)
    {
        //db_engine + whatever DSQL gives us
        $e->addClassSettings($this, 'provider', array(
        	'change_provider_settings' => array('', Settings::TYPE_CHECKBOX, null, 'change_provider_settings')
        ));
		$ps = Core::getClassesWithInterface('IProvider');
        foreach ($ps as $p)
        {
            $o = BObject::InvokeObjectByDynClass($p);
            if($o instanceof BProvider)
            {
                $o->handleEventRequestingClassSettings($e);
            }
        }
    }
    
    public function handleEventUpdateClassSettings(Event_UpdateClassSettings $e)
    {
        $data = $e->getClassSettings($this);
        if(!empty($data['change_provider_settings']))
        {
            SNotificationCenter::report('warning', 'changing_provider_settings');
			$ps = Core::getClassesWithInterface('IProvider');
            foreach ($ps as $p)
            {
                $o = BObject::InvokeObjectByDynClass($p);
                if($o instanceof BProvider)
                {
                    $o->handleEventUpdateClassSettings($e);
                }
            }
        }
    }
}
?>