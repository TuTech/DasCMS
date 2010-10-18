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
class UAdvancedMetaData
    implements
        Event_Handler_RequestingClassSettings,
        Event_Handler_UpdateClassSettings,
        Event_Handler_WillSendHeaders
{
    private static $keys = array(
        'localization' => array(
            'latitude'  => 'UAdvancedMetaData_latitude',
        	'longitude' => 'UAdvancedMetaData_longitude',
    		'placename' => 'UAdvancedMetaData_placename',
            'region'    => 'UAdvancedMetaData_region'
        )
    );

    private static $map = array(
        'site_location_location' => array('geo.location', 'ICBM'),
        'site_location_longitude' => array(),
        'site_location_latitude' => array(),
        'site_location_placename' => array('geo.placename'),
        'site_location_region' => array('geo.region')
    );

    public function handleEventRequestingClassSettings(Event_RequestingClassSettings $e)
    {
        foreach (self::$keys as $sect => $ks)
        {
            $data = array();
            foreach ($ks as $mk => $cc)
            {
                $data[$mk] = array(Core::settings()->get($cc), Settings::TYPE_TEXT, null,$mk);
            }
            $e->addClassSettings($this, $sect, $data);
        }
    }

    public function handleEventUpdateClassSettings(Event_UpdateClassSettings $e)
    {

        $data = $e->getClassSettings($this);
        foreach (self::$keys as $sect => $ks)
        {
            foreach ($ks as $mk => $cc)
            {
                if(isset($data[$mk]))
                {
                    Core::settings()->set($cc, $data[$mk]);
                }
            }
        }
    }

    public function handleEventWillSendHeaders(Event_WillSendHeaders $e)
    {
        $data = array();
        foreach (self::$keys as $sect => $ks)
        {
            foreach ($ks as $mk => $cc)
            {
                $data[$sect.'_'.$mk] = Core::settings()->get($cc);
            }
        }
        $data['site_location_location'] = '';
        if($data['site_location_longitude'] != '' && $data['site_location_latitude'] != '')
        {
            $data['site_location_location'] = $data['site_location_latitude'].';'.$data['site_location_longitude'];
        }
        $h = $e->getHeader();

        foreach (self::$map as $key => $metaKeys)
        {
            foreach ($metaKeys as $mkey)
            {
            	if(strlen($data[$key]) > 0){
            		$h->addMeta($data[$key], $mkey);
            	}
            }
        }
    }
}
?>