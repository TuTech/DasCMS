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
        Event_Handler_RequestingClassSettings,
        Event_Handler_UpdateClassSettings,
        Event_Handler_ContentAccess
{
    public function handleEventContentAccess(Event_ContentAccess $e)
    {
        if(Core::settings()->get('CFile_redirect_view_access') == 1
            && $e->Content instanceof CFile 
            && $e->Sender instanceof BView)
        {
            header('Location: '.SLink::base().'file.php/'.$e->Content->Alias.'/'.$e->Content->getFileName());
        }
    }
    
    public function handleEventRequestingClassSettings(Event_RequestingClassSettings $e)
    {
        $e->addClassSettings($this, 'image_rendering', array(
        	'width'						=> array(Core::settings()->get('CFile_image_width'), Settings::TYPE_TEXT, null, 'image_width'),
        	'height'					=> array(Core::settings()->get('CFile_image_height'), Settings::TYPE_TEXT, null, 'image_height'),
        	'rendering_method'			=> array(Core::settings()->get('CFile_image_rendering_method'), Settings::TYPE_SELECT, array(
            	'scale_aspect_to_fit_in_boundaries' => '0c', 
                'scale_aspect_and_crop' => '1c', 
                'scale_aspect_and_fill_background' => '1f', 
                'scale_by_stretch' => '1s'
    		), 'rendering_method'),
        	'background_color'			=> array(Core::settings()->get('CFile_image_background_color'), Settings::TYPE_TEXT, null, 'background_color'),
        	'CFile_image_quality'		=> array(Core::settings()->getOrDefault('CFile_image_quality', 75), Settings::TYPE_SELECT,
												array('minimal' => 1, 'low' => 25, 'medium' => 50, 'high' => 75, 'maximum' => 100), 'image_quality')
		));
        $e->addClassSettings($this, 'file_download', array(
        	'text_instead_of_filename'	=> array(Core::settings()->get('CFile_download_text'), Settings::TYPE_TEXT, null, 'text_instead_of_filename'),
        	'open_in_new_window'		=> array(Core::settings()->get('CFile_download_target_blank'), Settings::TYPE_CHECKBOX, null, 'open_download_in_new_window'),
            'force_download'			=> array(Core::settings()->get('CFile_force_download'), Settings::TYPE_CHECKBOX, null, 'force_download_for_all_files'),
            'redirect_view_access'		=> array(Core::settings()->get('CFile_redirect_view_access'), Settings::TYPE_CHECKBOX, null, 'redirect_html_access_to_file')
		));
    }
    
    public function handleEventUpdateClassSettings(Event_UpdateClassSettings $e)
    {
        $data = $e->getClassSettings($this);
        foreach(array('width', 'height') as $k)
        {
            if(!empty($data[$k]))
            {
                Core::settings()->set('CFile_image_'.$k, intval($data[$k]));
            }
        }
        foreach(array('CFile_image_background_color' => 'background_color',
        			  'CFile_download_text'          => 'text_instead_of_filename',
                      'CFile_download_target_blank'  => 'open_in_new_window',
                      'CFile_force_download'         => 'force_download',
                      'CFile_redirect_view_access'   => 'redirect_view_access') 
            as $ck => $dk)
        {
            if(isset($data[$dk]))
            {
                Core::settings()->set($ck, $data[$dk]);
            }
        }
        if(isset($data['rendering_method']) && in_array($data['rendering_method'], array('0c','1c','1f','1s')))
        {
            Core::settings()->set('CFile_image_rendering_method', $data['rendering_method']);
        }
        if(isset($data['CFile_image_quality']))
        {
            Core::settings()->set('CFile_image_quality', intval($data['CFile_image_quality']));
        }
    }
}
?>