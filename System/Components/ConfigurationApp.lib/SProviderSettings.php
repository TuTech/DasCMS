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
    extends BObject 
    implements
        HUpdateClassSettingsEventHandler,
        HRequestingClassSettingsEventHandler
{
    public function HandleRequestingClassSettingsEvent(ERequestingClassSettingsEvent $e)
    {
        //db_engine + whatever DSQL gives us
        $e->addClassSettings($this, 'provider', array(
        	'change_provider_settings' => array('', LConfiguration::TYPE_CHECKBOX, null, 'change_provider_settings')
        ));
		$ps = Core::getClassesWithInterface('IProvider');
        foreach ($ps as $p)
        {
            $o = BObject::InvokeObjectByDynClass($p);
            if($o instanceof BProvider)
            {
                $o->HandleRequestingClassSettingsEvent($e);
            }
        }
    }
    
    public function HandleUpdateClassSettingsEvent(EUpdateClassSettingsEvent $e)
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
                    $o->HandleUpdateClassSettingsEvent($e);
                }
            }
        }
    }
}
?>