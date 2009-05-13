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
class UBasicSettings
    extends BPlugin 
    implements 
        HRequestingClassSettingsEventHandler,
        HUpdateClassSettingsEventHandler
{
    private static $keys = array(
        'website_information' => array(
            'pagetitle'  => 'sitename',
        	'copyright' => 'copyright',
    		'meta_description' => 'meta_description',
            'meta_keywords'    => 'meta_keywords',
            'webmaster_email'    => 'webmaster'
        ),
        'website_rendering' => array(
            'template_for_page_rendering' => 'generator_content',
            'login_template' => 'login_template',
            'preview_image_quality' => 'preview_image_quality',
            'path_style_urls' => 'wellformed_urls'
        )
    );
    
    public function HandleRequestingClassSettingsEvent(ERequestingClassSettingsEvent $e)
    {
        foreach (self::$keys as $sect => $ks)
        {
            $data = array();
            foreach ($ks as $mk => $cc)
            {
                $options = array();
                switch($cc)
                {
                    case 'login_template':
                        $options[SLocalization::get('no_login_template')] = '';
                        //don't break
                    case 'generator_content':
                        $index = BContent::getIndex(CTemplate::CLASS_NAME, false);
                        foreach ($index as $alias => $cdata)
                        {
                            $options[$cdata[0].' ('.$alias.')'] = $alias; 
                        }
                        $data[$mk] = array(LConfiguration::get($cc), AConfiguration::TYPE_SELECT, $options);
                        break;
                       
                    case 'preview_image_quality':
                        $data[$mk] = array(LConfiguration::get($cc), AConfiguration::TYPE_SELECT,
                                            array('minimal' => 1, 'low' => 25, 'medium' => 50, 'high' => 75, 'maximum' => 100));
                        break;
                    case 'wellformed_urls':
                        $data[$mk] = array(LConfiguration::get($cc), AConfiguration::TYPE_CHECKBOX, null);
                        break;
                    default:
                        $data[$mk] = array(LConfiguration::get($cc), AConfiguration::TYPE_TEXT, null);
                        break;
                }
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
                $h->addMeta($data[$key], $mkey);
            }
        }
    }
}
?>