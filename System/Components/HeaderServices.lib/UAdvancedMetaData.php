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
    extends BPlugin
    implements
        HRequestingClassSettingsEventHandler,
        HUpdateClassSettingsEventHandler,
        HWillSendHeadersEventHandler
{
    private static $keys = array(
        'site_location' => array(
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

    public function HandleRequestingClassSettingsEvent(ERequestingClassSettingsEvent $e)
    {
        foreach (self::$keys as $sect => $ks)
        {
            $data = array();
            foreach ($ks as $mk => $cc)
            {
                $data[$mk] = array(LConfiguration::get($cc), LConfiguration::TYPE_TEXT, null,$mk);
            }
            $e->addClassSettings($this, $sect, $data);
        }
    }

    public function HandleUpdateClassSettingsEvent(EUpdateClassSettingsEvent $e)
    {

        $data = $e->getClassSettings($this);
        foreach (self::$keys as $sect => $ks)
        {
            foreach ($ks as $mk => $cc)
            {
                if(isset($data[$mk]))
                {
                    LConfiguration::set($cc, $data[$mk]);
                }
            }
        }
    }

    public function HandleWillSendHeadersEvent(EWillSendHeadersEvent $e)
    {
        $data = array();
        foreach (self::$keys as $sect => $ks)
        {
            foreach ($ks as $mk => $cc)
            {
                $data[$sect.'_'.$mk] = LConfiguration::get($cc);
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