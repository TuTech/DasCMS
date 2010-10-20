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
    implements 
        Event_Handler_RequestingClassSettings,
        Event_Handler_UpdateClassSettings,
        Event_Handler_WillSendHeaders
{
    public function handleEventRequestingClassSettings(Event_RequestingClassSettings $e)
    {
        $e->addClassSettings($this, 'google_services', array(
        	'verify_v1' => array(Core::Settings()->get('google_verify_header'), Settings::TYPE_TEXT, null, 'google_verify_header'),
        	'google_maps_key' => array(Core::Settings()->get('google_maps_key'), Settings::TYPE_TEXT, null, 'google_maps_key'),
        	'load_maps_support' => array(Core::Settings()->get('google_load_maps_support'), Settings::TYPE_CHECKBOX, null, 'google_load_maps_support')
        ));
    }
    
    public function handleEventUpdateClassSettings(Event_UpdateClassSettings $e)
    {
        
        $data = $e->getClassSettings($this);
            if(isset($data['verify_v1']))
        {
            Core::Settings()->set('google_verify_header', $data['verify_v1']);
        }
        if(isset($data['load_maps_support']))
        {
            Core::Settings()->set('google_load_maps_support', $data['load_maps_support']);
        }
        if(isset($data['google_maps_key']))
        {
            Core::Settings()->set('google_maps_key', $data['google_maps_key']);
        }
    }
    
    public function handleEventWillSendHeaders(Event_WillSendHeaders $e)
    {
        $confMeta = array(
            'google_verify_header' => 'verify-v1',
        );
        foreach($confMeta as $key => $metaKey)
        {
            $val = Core::Settings()->get($key);
            if(!empty($val))
            {
                $e->getHeader()->addMeta($val, $metaKey);
            }
        }
        if(Core::Settings()->get('google_maps_key') != '')
        {
            $e->getHeader()->addScript('text/javascript', 'http://maps.google.com/maps?file=api&amp;v=2&amp;key='.Core::Settings()->get('google_maps_key'));
        }
        if(Core::Settings()->get('google_load_maps_support') != '')
        {
            $e->getHeader()->addScript('text/javascript', 'System/WebsiteSupport/JavaScript/GoogleMapsSupport.js');
        }
    }
}
?>