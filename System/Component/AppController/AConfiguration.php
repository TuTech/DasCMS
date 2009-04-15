<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2009-03-11
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage AppController
 */
class AConfiguration
    extends 
        BAppController 
    implements 
        IGlobalUniqueId  
{
    const GUID = 'org.bambuscms.applications.configuration';
    private $keys = array(
        //settings
        'sitename', 'logo','webmaster', 'copyright','cms_color',
        'cms_text_color','dateformat',
        'logout_on_exit', 'confirm_for_exit','generator_content',  
        'use_wysiwyg', 'wellformed_urls',
        'login_template','mail_webmaster_on_error',
        'PAuthentication','PAuthorisation',
        //database_settings
        'db_server', 'db_user', 'db_password', 'db_name', 'db_table_prefix','db_port',
        //meta_data
        'meta_description', 'meta_keywords',
        'timezone', 'locale','preview_image_quality',
        //logs
        'logAccess', 'logChanges'
    );
    private $checkboxes = array(
        'logout_on_exit', 
        'confirm_for_exit',  
        'use_db',
    	'wellformed_urls',
        'chdbpasswd',
        'logAccess', 
        'logChanges',
        'use_wysiwyg',
    	'mail_webmaster_on_error'
    );
    
    /**
     * @return string
     * (non-PHPdoc)
     * @see System/Component/Interface/IGlobalUniqueId#getClassGUID()
     */
    public function getClassGUID()
    {
        return self::GUID;
    }
    
    public function save(array $config)
    {
        parent::requirePermission('org.bambuscms.configuration.set');
        foreach ($this->checkboxes as $cb) 
        {
        	$config[$cb] = (isset($config[$cb]) && $config[$cb] == 'on') 
        	    ? '1' : '';
        }
        foreach($this->keys as $key)
        {
            if(isset($config[$key]) 
                && ($key != 'db_password' || RSent::hasValue('chdbpasswd')))
            {
                LConfiguration::set($key, $config[$key]);
            }
        }
        SNotificationCenter::report('message', 'configuration_saved');
    }
}
?>