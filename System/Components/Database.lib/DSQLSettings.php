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
        	'change_database_settings' => array('', LConfiguration::TYPE_CHECKBOX, null, 'change_database_settings'),
           	'engine' => array(LConfiguration::get('db_engine'), LConfiguration::TYPE_SELECT, DSQL::getEngines(), 'db_engine')
        ));
        DSQL::getSharedInstance()->HandleRequestingClassSettingsEvent($e);
    }
    
    public function HandleUpdateClassSettingsEvent(EUpdateClassSettingsEvent $e)
    {
        $data = $e->getClassSettings($this);
        if(!empty($data['change_database_settings']))
        {
            SNotificationCenter::report('warning', 'changing_database_settings');
            if(isset($data['engine']) && in_array($data['engine'], DSQL::getEngines()))
            {
                LConfiguration::set('db_engine', $data['engine']);
            }
            DSQL::getSharedInstance()->HandleUpdateClassSettingsEvent($e);
        }
    }
}
?>