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
    const TYPE_TEXT = 1;
    const TYPE_CHECKBOX = 2;
    const TYPE_SELECT = 3;
    const TYPE_PASSWORD = 4;
    
    const GUID = 'org.bambuscms.applications.configuration';
    private  $data = array(
	);
    
    private  $sentdata = array(
	);
    
	public function addSettings($toSection, $withOwnerClass, $settings)
	{
	    if(!isset($this->data[$toSection]))
	    {
	        $this->data[$toSection] = array();
	    }
	    $this->data[$toSection][$withOwnerClass] = $settings;
	}
	
	
    /**
     * @return string
     * (non-PHPdoc)
     * @see System/Component/Interface/IGlobalUniqueId#getClassGUID()
     */
    public function getClassGUID()
    {
        return self::GUID;
    }
    
    public function getSettings()
    {
        $e = new ERequestingClassSettingsEvent($this);
        $dataset = array();
        foreach($this->data as $sect => $classesData)
        {
            $dataset[$sect] = array();
            foreach($classesData as $class => $classSettings)
            {
                $pfx = '@'.md5($class).'_';
                foreach($classSettings as $key => $config)
                {
                    list($value, $type, $options) = $config;
                    $dataset[$sect][$pfx.$key] =  array($class.'_'.$key, $value, $type, $options);
                }
            }
        }
        return $dataset;
    }
    
    public function save(array $config)
    {
        parent::requirePermission('org.bambuscms.configuration.set');
        
        //fetch class settings
        foreach ($config as $key => $value)
        {
            if(substr($key,0,1) == '@')
            {
                $classKey = substr($key,1,32);
                $confKey = substr($key,34);
                if(!isset($this->sentdata[$classKey]))
                {
                    $this->sentdata[$classKey] = array();
                }
                $this->sentdata[$classKey][$confKey] = $value;
            }
        }
        
        //correct checkbox data for class settings
        //every checkbox "@..." has its presence indicator "_@..."
        foreach ($config as $key => $value)
        {
            if(substr($key,0,2) == '_@')
            {
                $classKey = substr($key,2,32);
                $confKey = substr($key,35);
                if(!isset($this->sentdata[$classKey]))
                {
                    $this->sentdata[$classKey] = array();
                }
                //set to false if empty
                $this->sentdata[$classKey][$confKey] = !empty($this->sentdata[$classKey][$confKey]);
            }
        }
        ob_start();
        $e = new EUpdateClassSettingsEvent($this, $this->sentdata);
        SNotificationCenter::report('message', 'configuration_saved');
    }
}
?>