<?php 
/**
 * @package Bambus
 * @subpackage Deprecated 
 * @deprecated 
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 12.06.2006
 * @license GNU General Public License 3
 */
class Template extends Bambus implements IShareable
{
	//IShareable
	const Class_Name = 'Template';
	public static $sharedInstance = NULL;
	private static $initializedInstance = false;
	public static function alloc()
	{
		$class = self::Class_Name;
		if(self::$sharedInstance == NULL && $class != NULL)
		{
			self::$sharedInstance = new $class();
		}
		return self::$sharedInstance;
	}
    
    function init()
    {
    	if(!self::$initializedInstance)
    	{
    		if(defined('BAMBUS_DEBUG'))printf("\n<!--[%s init]-->\n", self::Class_Name);
	    	self::$initializedInstance = true;
			$this->BCMSString = BCMSString::alloc();
			$this->FileSystem = FileSystem::alloc();

			$this->BCMSString->init();
			$this->FileSystem->init();
    	}
    }
	//end IShareable

    var $Translation;
    var $env = array();
    
    function __construct()
    {
        parent::Bambus();
    }
    
    public function __sleep()
    {
    	//do not serialize
    	return array();
    }
    	
    function setEnv($key,$value)
    {
        $this->env[$key] = $value;
    }
    
    function getEnv($key)
    {
        return $this->env[$key];    
    }
    
    function addToEnv($key, $value)
    {
    	if(!empty($this->env[$key]))
	        $this->env[$key] .= $value;
	    else
	    	$this->env[$key] = $value;       
    }
    
    function exportEnv()
    {
        return $this->env;
    }
    
    function parseObject($template, $Object, $type = 'content')
    {
        if($type == 'content')
        {
            $template = $this->FileSystem->read(parent::pathToFile('template',$template));
        }
        elseif($type == 'system')
        {
            $template = $this->FileSystem->read(parent::pathToFile('systemTemplate',$template));
        }
    	$P = Parser::alloc();
    	$P->init();
    	return $P->parse($template, $Object);
    }
    
    function parse($template, $vars, $type = 'content')
    {
//		$pstart = microtime(true);
//		printf("\n\n<!-- begin parsing of \"%s\"-->", $template);
		$templatefile = $template;
        $env = $this->env;
        foreach(array_keys($vars) as $key)
        {
            $env[$key] = $vars[$key];
        }
        if($type == 'content')
        {
            $template = $this->FileSystem->read(parent::pathToFile('template',$template));
        }
        elseif($type == 'system')
        {
            $template = $this->FileSystem->read(parent::pathToFile('systemTemplate',$template));
        }
        $res = ($this->BCMSString->bsprintv($template, $env));
//		$pstop = microtime(true);
//		printf("\n<!-- end parsing of \"%s\" after %fsec-->\n\n", $templatefile, $pstop-$pstart);
		
		return $res;
    }
}
?>