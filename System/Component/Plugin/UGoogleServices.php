<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2009-05-12
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage Plugin
 */
class UGoogleServices 
    extends BPlugin 
    implements 
        HRequestingClassSettingsEventHandler,
        HUpdateClassSettingsEventHandler,
        HWillSendHeadersEventHandler
{
    public function HandleRequestingClassSettingsEvent(ERequestingClassSettingsEvent $e)
    {
        $e->addClassSettings($this, 'google_services', array(
        	'verify_v1' => array(LConfiguration::get('google_verify_header'), AConfiguration::TYPE_TEXT, null)
        ));
    }
    
    public function HandleUpdateClassSettingsEvent(EUpdateClassSettingsEvent $e)
    {
        
        $data = $e->getClassSettings($this);
        if(isset($data['verify_v1']))
        {
            LConfiguration::set('google_verify_header', $data['verify_v1']);
        }
    }
    
    public function HandleWillSendHeadersEvent(EWillSendHeadersEvent $e)
    {
        $confMeta = array(
            'google_verify_header' => 'verify-v1',
        );
        foreach($confMeta as $key => $metaKey)
        {
            $val = LConfiguration::get($key);
            if(!empty($val))
            {
                $e->getHeader()->addMeta($val, $metaKey);
            }
        }
    }
}
?>