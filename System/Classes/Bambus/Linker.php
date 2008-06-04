<?php 
/**
 * @package Bambus
 * @subpackage Deprecated 
 * @deprecated 
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 14.05.2007
 * @license GNU General Public License 3
 */
class Linker extends Bambus implements IShareable
{
	//IShareable
	const Class_Name = 'Linker';
	public static $sharedInstance = NULL;
	private static $initializedInstance = false;
	/**
	 * @return Linker
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
	 * @return Linker
	 */
	function init()
    {
    	if(!self::$initializedInstance)
    	{
	    	if(defined('BAMBUS_DEBUG'))printf("\n<!--[%s init]-->\n", self::Class_Name);
	    	self::$initializedInstance = true;
			$this->Configuration = Configuration::alloc();

			$this->Configuration->init();
    	}
    	return $this;
    }
	//end IShareable

    public
    	 $get
    	,$post
    	,$session
    	,$files;
    
    function createFormInputs(){}
    
    public function myURL()
    {
    	return $this->createQueryString();
    }
    
    public function myFormURL()
    {
    	return $this->createQueryString(array(), true); //true for the $beCruel attribute 
    }
    
    public function __sleep()
    {
    	//do not serialize
    	return array();
    }
    	
    public function myBase()
    {
    	$base = $this->Configuration->get('cms_uri');
    	if(empty($base))
    	{
	    	$base = './';
	    	$scrn = getenv('SCRIPT_NAME');//use $alt_cms_uri = dirname($_SERVER['REQUEST_URI'].'');
	    	$srvn = getenv('SERVER_NAME');
	    	$prot = getenv('SERVER_PROTOCOL');
			if(!empty($prot))
				$prot = strtolower(substr($prot, 0, strpos($prot,'/')));
			else
				$prot = 'http';
	    	if(!empty($scrn) && !empty($srvn))
	    	{
				$management = (defined('BAMBUS_ACCESS_TYPE') && BAMBUS_ACCESS_TYPE == 'management');
				$scrn = dirname($scrn);
				$temp = explode("/", $scrn);
				if($management)
					array_pop($temp);
				for($i = 0; $i < count($temp); $i++)
					if(empty($temp[$i]))unset($temp[$i]);
				$scrn = sprintf('%s://%s/%s/', $prot,$srvn,implode("/", $temp));
				$base = $scrn;
	    	}
    	}
    	return $base;
    }
    
    public function createQueryString($changes = array(), $beCruel = false, $target = '')
    {
        $get = $this->get;
        $beCruel = $beCruel && empty($target);
        $query =  defined('BAMBUS_ACCESS_TYPE') && BAMBUS_ACCESS_TYPE == 'management' ? 'Management/' : '';
        $query .= $target;
        $query .= (BAMBUS_NICE_URLS && !$beCruel) ? '' : '?';
        
        //remove use-once and empty variables 
        foreach($get as $key => $nil)
        {
            if(substr($key, 0, 1) == '_' || trim($get[$key]) == '')
                unset($get[$key]);
        }
        //apply changes
        foreach($changes as $key => $value)
        {
            if(trim($value) != '')
                $get[$key] = $value;
            else
                unset($get[$key]);
        }
        $token =  '';
        $tokchar = (BAMBUS_NICE_URLS && !$beCruel) ? '/' : '&amp;';
        $assignchar = (BAMBUS_NICE_URLS && !$beCruel) ? '/' : '=';
        foreach(array_keys($get) as $key)
        {
            $query .= $token.
            			htmlentities(urlencode(utf8_encode($key))).
            			$assignchar.
            			htmlentities(urlencode(utf8_encode($get[$key])));
            $token = $tokchar;
        }
        
        $query = ($query == '') ? '?' : str_replace('%3A', ':', $query);
        return $query;
    }
    
    public function allowCallFromTemplate($fx)
    {
    	return in_array($fx, array('get','post','session','files','myBase', 'myURL', 'myFormURL'));
    }
    
    public function set($var, $key, $value)
    {
    	switch($var)
    	{
    		case 'get':
    			return ($this->get[$key] = $value);
    		case 'post':
    			return ($this->post[$key] = $value);
    		case 'session':
    			return ($this->session[$key] = $value);
    		default:
    			return false;
    	}
    }
    
    public function dump()
    {
    	echo '<pre>';
    	var_dump($this->get);
    	echo '</pre>';
    }
    
    public function get($key = '')		
    {
    	if(is_array($key))
    	{
    		$v = array_values($key);
    		$key = $v[0];
    	}
    	return (isset($this->get[$key])) ? $this->get[$key] : '';
    }
    
    public function post($key = '')		
    {
    	if(is_array($key))
    	{
    		$v = array_values($key);
    		$key = $v[0];
    	}
    	return (isset($this->post[$key])) ? $this->post[$key] : '';
    }
    
    public function session($key = '')		
    {
    	if(is_array($key))
    	{
    		$v = array_values($key);
    		$key = $v[0];
    	}
    	return (isset($this->session[$key])) ? $this->session[$key] : '';
    }
    
    public function files($key = '')		
    {
    	if(is_array($key))
    	{
    		$v = array_values($key);
    		$key = $v[0];
    	}
    	return (isset($this->files[$key])) ? $this->files[$key] : '';
    }
}
?>