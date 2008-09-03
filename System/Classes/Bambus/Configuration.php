<?php
/**
 * @package Bambus
 * @subpackage Deprecated 
 * @deprecated 
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 22.09.2006
 * @license GNU General Public License 3
 */
class Configuration extends Bambus implements IShareable
{
	//IShareable
	const Class_Name = 'Configuration';
	public static $sharedInstance = NULL;
	private static $initializedInstance = false;
	/**
	 * @return Configuration
	 */
	public static function alloc()
	{
		$class = self::Class_Name;
		if(self::$sharedInstance == NULL && $class != NULL)
		{
			self::$sharedInstance = new $class();
		}
		return self::$sharedInstance;
	}
    
	/**
	 * @return Configuration
	 */
	function init()
    {
    	if(!self::$initializedInstance)
    	{
    		if(defined('BAMBUS_DEBUG'))printf("\n<!--[%s init]-->\n", self::Class_Name);
	    	self::$initializedInstance = true;
    	}
    	return $this;
    }
	//end IShareable

    private $configuration;

	public function __construct()
	{
		parent::Bambus();
	}

    public function __sleep()
    {
    	//do not serialize
    	return array();
    }
    	
    public function loadVars($i, $dont_care, $about, $these_variables)
    {
        $this->configuration = array();
        $file = SPath::SYSTEM.'configuration/system.php';
        $cfg = &$this->configuration;
        $fileData = DFileSystem::Load($file);
        $temp = explode("\n", $fileData);
        unset($temp[0]);
        if(isset($temp[count($temp)-1]) && substr($temp[count($temp)-1], -2) == '?>') array_pop($temp);
        $cfg = unserialize(implode('',$temp));
    }

    public function as_array()
    {
        return $this->configuration;
    }
	
	//var access per function
    public function get($var)
    {
    	return strval((isset($this->configuration[$var])) ? $this->configuration[$var] : '');
    }
    
    public function set($var, $value, $save = false)
    {
        $this->configuration[$var] = $value;
        if($save)
        {
            $this->save();
        }
    }
    //var access as property
    public function __get($var)
    {
    	return $this->get($var);
    }
    
    public function exists($var)
    {
    	return isset($this->configuration[$var]);
    }
    
    public function __set($var, $value)
    {
         $this->configuration[$var] = $value;   	
    }
    
    //save changed config
    public function save()
    {
    	SNotificationCenter::report('message', 'configuration_saved');
        $file = SPath::SYSTEM.'configuration/system.php';
        return DFileSystem::SaveData($file, $this->configuration);
    }
}
?>
