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
        	'width' => array(LConfiguration::get('CFile_image_width'), AConfiguration::TYPE_TEXT, null, 'image_width'),
        	'height' => array(LConfiguration::get('CFile_image_height'), AConfiguration::TYPE_TEXT, null, 'image_height'),
        	'rendering_method' => array(LConfiguration::get('CFile_image_rendering_method'), AConfiguration::TYPE_SELECT, array(
            	'scale_aspect_to_fit_in_boundaries' => '0c', 
                'scale_aspect_and_crop' => '1c', 
                'scale_aspect_and_fill_background' => '1f', 
                'scale_by_stretch' => '1s'
    		), 'rendering_method'),
        	'background_color' => array(LConfiguration::get('CFile_image_background_color'), AConfiguration::TYPE_TEXT, null, 'background_color'),
        	'CFile_image_quality' => array(LConfiguration::get('CFile_image_quality'), AConfiguration::TYPE_SELECT,
                                            array('minimal' => 1, 'low' => 25, 'medium' => 50, 'high' => 75, 'maximum' => 100), 'image_quality')
		));
        $e->addClassSettings($this, 'file_download_links', array(
        	'text_instead_of_filename' => array(LConfiguration::get('CFile_download_text'), AConfiguration::TYPE_TEXT, null, 'text_instead_of_filename'),
        	'open_in_new_window' => array(LConfiguration::get('CFile_download_target_blank'), AConfiguration::TYPE_CHECKBOX, null, 'open_download_in_new_window')
		));
    }
    
    public function HandleUpdateClassSettingsEvent(EUpdateClassSettingsEvent $e)
    {
        $data = $e->getClassSettings($this);
        foreach(array('width', 'height', 'quality') as $k)
        {
            if(!empty($data[$k]))
            {
                LConfiguration::set('CFile_image_'.$k, intval($data[$k]));
            }
        }
        foreach(array('CFile_image_background_color' => 'background_color',
        			  'CFile_download_text' => 'text_instead_of_filename',
                      'CFile_download_target_blank'=>'open_in_new_window') as $ck => $dk)
        {
            if(isset($data[$dk]))
            {
                LConfiguration::set($ck, $data[$dk]);
            }
        }
        if(isset($data['rendering_method']) && in_array($data['rendering_method'], array('0c','1c','1f','1s')))
        {
            LConfiguration::set('CFile_image_rendering_method', $data['rendering_method']);
        }
    }
}
?>