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
class UCFileConfig 
    extends BPlugin 
    implements 
        HRequestingClassSettingsEventHandler,
        HUpdateClassSettingsEventHandler
{
    public function HandleRequestingClassSettingsEvent(ERequestingClassSettingsEvent $e)
    {
        $e->addClassSettings($this, 'image_rendering', array(
        	'width' => array(LConfiguration::get('CFile_image_width'), AConfiguration::TYPE_TEXT, null),
        	'height' => array(LConfiguration::get('CFile_image_height'), AConfiguration::TYPE_TEXT, null),
        	'rendering_method' => array(LConfiguration::get('CFile_image_rendering_method'), AConfiguration::TYPE_SELECT, array(
            	'scale_aspect_to_fit_in_boundaries' => '0c', 
                'scale_aspect_and_crop' => '1c', 
                'scale_aspect_and_fill_background' => '1f', 
                'scale_by_stretch' => '1s'
    		)),
        	'background_color' => array(LConfiguration::get('CFile_image_background_color'), AConfiguration::TYPE_TEXT, null)
        ));
    }
    
    public function HandleUpdateClassSettingsEvent(EUpdateClassSettingsEvent $e)
    {
        $data = $e->getClassSettings($this);
        if(!empty($data['width']))
        {
            LConfiguration::set('CFile_image_width', intval($data['width']));
        }
        if(!empty($data['height']))
        {
            LConfiguration::set('CFile_image_height', intval($data['height']));
        }
        if(isset($data['rendering_method']) && in_array($data['rendering_method'], array('0c','1c','1f','1s')))
        {
            LConfiguration::set('CFile_image_rendering_method', $data['rendering_method']);
        }
        if(isset($data['background_color']))
        {
            LConfiguration::set('CFile_image_background_color', $data['background_color']);
        }
    }
}
?>