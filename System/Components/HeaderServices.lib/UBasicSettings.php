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
    implements 
        Event_Handler_RequestingClassSettings,
        Event_Handler_UpdateClassSettings,
        Event_Handler_WillSendHeaders
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
        'management' => array(
            'enable_management_cache_manifest' => 'enable_management_cache_manifest'
        )
    );
    
    public function handleEventRequestingClassSettings(Event_RequestingClassSettings $e)
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
                        $index = Controller_Content::getInstance()->contentIndex(CTemplate::CLASS_NAME);
                        foreach ($index as $alias => $cdata)
                        {
                            $options[$cdata[0].' ('.$alias.')'] = $alias; 
                        }
                        $data[$mk] = array(Core::settings()->get($cc), Settings::TYPE_SELECT, $options, $cc);
                        break;
                       
                    case 'wellformed_urls':
                    case 'enable_management_cache_manifest':
                        $data[$mk] = array(Core::settings()->get($cc), Settings::TYPE_CHECKBOX, null, $cc);
                        break;
                    default:
                        $data[$mk] = array(Core::settings()->get($cc), Settings::TYPE_TEXT, null, $cc);
                        break;
                }
            }
            $e->addClassSettings($this, $sect, $data);
        }
    }
    
    public function handleEventUpdateClassSettings(Event_UpdateClassSettings $e)
    {
        try
        {
            $db = DSQL::getInstance();
            $db->beginTransaction();
            
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
            $aliases = array(Core::settings()->get('generator_content'));
            $lt = Core::settings()->get('login_template');
            if(!empty($lt))
            {
                $aliases[] = $lt;
            }
            $coco = Controller_Content::getInstance();
            $coco->releaseContentChainsToClass($this);
            $coco->chainContentsToClass($this, $aliases);
            $db->commit();
        }
        catch (XDatabaseException $e)
        {
            $e->rollback();
            throw $e;
        }
    }
    
    public function handleEventWillSendHeaders(Event_WillSendHeaders $e)
    {
        $confMeta = array(
            'copyright' => 'copyright',
            'publisher' => 'DC.publisher',
            'generator' => 'generator'
        );
        foreach($confMeta as $key => $metaKey)
        {
            $v = $key == 'generator' ? BAMBUS_VERSION : Core::settings()->get($key);
            if(!empty($v))
            {
                $e->getHeader()->addMeta($v,$metaKey);
            }
        }
    }
}
?>