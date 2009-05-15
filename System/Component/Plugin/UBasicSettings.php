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
        HUpdateClassSettingsEventHandler,
        HWillSendHeadersEventHandler
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
            'path_style_urls' => 'wellformed_urls'
        ),
        'logging' => array(
            'log_page_accesses' => 'log_page_accesses'
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
                       
                    case 'wellformed_urls':
                    case 'log_page_accesses':
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
        $confMeta = array(
            'copyright' => 'copyright',
            'publisher' => 'DC.publisher',
            'generator' => 'generator'
        );
        foreach($confMeta as $key => $metaKey)
        {
            $v = $key == 'generator' ? BAMBUS_VERSION : LConfiguration::get($key);
            if(!empty($v))
            {
                $e->getHeader()->addMeta($v,$metaKey);
            }
        }
    }
}
?>