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
class DSQLSettings
	extends BObject
    implements
        HUpdateClassSettingsEventHandler,
        HRequestingClassSettingsEventHandler
{
    public function HandleRequestingClassSettingsEvent(ERequestingClassSettingsEvent $e)
    {
        //db_engine + whatever DSQL gives us
        $e->addClassSettings($this, 'database', array(
        	'change_database_settings' => array('', Settings::TYPE_CHECKBOX, null, 'change_database_settings'),
           	'engine' => array(Core::settings()->get('db_engine'), Settings::TYPE_SELECT, DSQL::getEngines(), 'db_engine')
        ));
        DSQL::getInstance()->HandleRequestingClassSettingsEvent($e);
    }
    
    public function HandleUpdateClassSettingsEvent(EUpdateClassSettingsEvent $e)
    {
        $data = $e->getClassSettings($this);
        if(!empty($data['change_database_settings']))
        {
            SNotificationCenter::report('warning', 'changing_database_settings');
            if(isset($data['engine']) && in_array($data['engine'], DSQL::getEngines()))
            {
                Core::settings()->set('db_engine', $data['engine']);
            }
            DSQL::getInstance()->HandleUpdateClassSettingsEvent($e);
        }
    }
}
?>