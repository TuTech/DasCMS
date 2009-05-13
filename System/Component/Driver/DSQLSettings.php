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
    extends BDriver 
    implements
        HUpdateClassSettingsEventHandler,
        HRequestingClassSettingsEventHandler
{
    public function HandleRequestingClassSettingsEvent(ERequestingClassSettingsEvent $e)
    {
        //db_engine + whatever DSQL gives us
        $e->addClassSettings($this, 'database', array('engine' => array(LConfiguration::get('db_engine'), AConfiguration::TYPE_SELECT, DSQL::getEngines())));
        DSQL::getSharedInstance()->HandleRequestingClassSettingsEvent($e);
    }
    
    public function HandleUpdateClassSettingsEvent(EUpdateClassSettingsEvent $e)
    {
        $data = $e->getClassSettings($this);
        if(isset($data['engine']) && in_array($data['engine'], DSQL::getEngines()))
        {
            LConfiguration::set('db_engine', $data['engine']);
        }
        DSQL::getSharedInstance()->HandleUpdateClassSettingsEvent($e);
    }
}
?>