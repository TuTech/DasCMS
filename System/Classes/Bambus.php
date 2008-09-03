<?php 
/**
 * @package Bambus
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @license GNU General Public License 3
 */

/**
 * Class Autoloader (PHP magic function)
 *
 * @param string $className 
 */
function __autoload($className)
{
	$sc = substr($className,1,1);
	if($sc == strtoupper($sc))
	{
		//new class loader
		$Components = array(
//			'A' => 'AppController',
			'B' => 'Base',
			'C' => 'Content',
			'D' => 'Driver',
			'E' => 'Event',
			'H' => 'EventHandler',
			'I' => 'Interface',
            'L' => 'Legacy',
			'M' => 'Manager',
			'N' => 'Navigator',
			'Q' => 'Query',
			'S' => 'System',
			'W' => 'Widget',
			'X' => 'Exception'
		);
		$fc = substr($className,0,1); //first char
		$sc = substr($className,1,1); //second char
		if($sc == strtolower($sc))//valid class name begins with to uppercase chars 
		{						  //use the content class as default
			$fc = 'M';
			$className = 'M'.$className;
		}
		$file = sprintf("./System/Component/%s/%s.php", $Components[$fc], $className);
		if(array_key_exists($fc, $Components) && file_exists($file))
		{
			include_once($file);
		}
	}
	else
	{
		//old class loader
		$file = sprintf('System/Classes/Bambus/%s.php', $className);
		if(file_exists($file))
		{
			include_once($file);
		}
		else
		{
			SNotificationCenter::report('alert', 'could_not_load_class'.$className);
		}
		
	}
}
/**
 * @package Bambus
 * @subpackage Deprecated 
 * @deprecated 
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 12.06.2006
 * @license GNU General Public License 3
 */
class Bambus extends BObject 
{
	//inherit sharable
	const Class_Name = NULL;

	///////////////////////////// 	
    //autoload required classes//
    /////////////////////////////

    //public
    var 
    	$Configuration;

	//include all class-root-level classes
	     

    //private
    protected $autoloadClasses = array(
		'Configuration'
		,'Linker'
	);
	
	protected $managementAutoloadClasses = array(
	);
	
    public $loadedClasses = array();
    
    ///////////////////////
	//Init some variables//
	///////////////////////

	//protected
    public $paths,
    	$files,
    	$get = array(),
    	$post = array(), 
    	$session = array(), 
    	$uploadfiles = array(),
    	$notifications = array();
    
    
    
    
    
    
    
    ////////////////////////////////
    //define environment variables//
    ////////////////////////////////
    
    function Bambus()
    {
		//move to config
        $this->paths = array(
            'systemApplication' => 		'./System/Applications/',
        );
        $this->files = array();
    	if(!defined('BAMBUS_VERSION'))
        {
            define ('BAMBUS_VERSION', '0.16.0-DEV20080314-CHIMERA');
			if(!defined('PHP_PATH_SEPARATOR'))
				define('PHP_PATH_SEPARATOR', '/');
				
			if(!defined('BAMBUS_CMS_VERSION_ID'))
				define('BAMBUS_CMS_VERSION_ID', 'Bambus CMS 0.20.DEV20080314-CHIMERA');
				
			if(!defined('BAMBUS_CMS_ROOTDIR'))
				define('BAMBUS_CMS_ROOTDIR',getcwd());
            
	        if(!defined('BAMBUS_VERSION_NAME'))
	            define ('BAMBUS_VERSION_NAME', 'Bambus CMS '.constant('BAMBUS_VERSION'));
	        
	        if(!defined('BAMBUS_EXEC_START'))
	            define ('BAMBUS_EXEC_START', microtime(true));
	        
	        if(!defined('BAMBUS_CMS_ROOT'))
	            define ('BAMBUS_CMS_ROOT', getcwd());
	
	
			//TODO: set by config
			setlocale (LC_ALL, 'de_DE');
			date_default_timezone_set('Europe/Berlin');
			
	        if(defined('BAMBUS_ACCESS_TYPE') && constant('BAMBUS_ACCESS_TYPE') == 'management')
			{
				$this->autoloadClasses = array_merge($this->autoloadClasses, $this->managementAutoloadClasses);
			}
        }
    }   
    
    public function __sleep()
    {
    	//do not serialize
    	return array();
    }
    	
    function loadVars($get,$post,$session,$uploadfiles)
    {
        $this->get = $get;
        $this->post = $post;
        $this->session = $session;
        $this->uploadfiles = $uploadfiles;
    }
   	
   	///////////////////////////////
	//load and initialize classes//
	///////////////////////////////    
   	
   	function using($classNames)
   	{
		//first init
		$success = true;
		$classNames = (is_array($classNames)) ? $classNames : array($classNames);
		$phpVersionArray = explode('.', phpversion());
		$phpVersion = $phpVersionArray[0];
		$path = substr(__FILE__,0,-4).'/';
		foreach($classNames as $className)
		{
			if(file_exists($path.$className.'.php'.$phpVersion.'.php'))
			{
				//is there a version of the class optimized for this php-version
   				require_once($path.$className.'.php'.$phpVersion.'.php');
			}
			else
			{
				require_once($path.$className.'.php');
			}
		}
		foreach($classNames as $className)
		{
   			if(class_exists($className))
   			{
   				$class = new $className;
   				$this->{$className} = $class->alloc();
   				$this->{$className}->init();
   				unset($class);
   				$this->loadedClasses[$className] = $className;
   			}
			
		}		
		//then link
		foreach($this->loadedClasses as $className)
		{
   			//need some user input variables?
	   		if(method_exists($this->{$className}, 'loadVars'))
	   		{
	   			$this->{$className}->loadVars($this->get,$this->post,$this->session,$this->uploadfiles);
	   		}
		}
   		return $success;
   	}
  	    

   	//////////////////////////////
	//initialize input variables//
    //////////////////////////////
    
    function initialize(&$lget,$post,$session,$files, $rawUrl = false)
    {
    	//nice url reformating
    	$this->using(array('Configuration', 'Linker'));
        if(!defined('BAMBUS_NICE_URLS'))
            define ('BAMBUS_NICE_URLS', ($this->Configuration->get('404redirect') == '1' && (!defined('BAMBUS_ACCESS_TYPE') || BAMBUS_ACCESS_TYPE != 'management')));
    	if(!empty($rawUrl))
    	{
    		$cms_uri = $this->Linker->myBase();
    		$siteUrlArr = parse_url($rawUrl);
    		$cmsUrlArr = parse_url($cms_uri);
			$sitePath = ($siteUrlArr['path']);
			$cmsPath = ($cmsUrlArr['path']);
			//echo $sitePath.'--'.$cmsPath;
    		if(strlen($sitePath) > strlen($cmsPath))
    		{
    			if(substr($sitePath,0,strlen($cmsPath)) == $cmsPath)
    			{
    				//redirect happened
    				$redirectParams = substr($sitePath,strlen($cmsPath));
    				$redirectParams = (substr($redirectParams, -1) == '/') ? substr($redirectParams, 0, -1) : $redirectParams;
    				$vars = explode('/', $redirectParams);
    				$last = false;
   					$overwrite = false;
    				foreach($vars as $var)
    				{
 						if($last == false)
						{
		   					$overwrite = false;
							if(!isset($lget[$var]))
							{
								$lget[$var] = ''; //initialize var
								$overwrite = true;
							}
							$last = $var;
						}
						else
						{
							$lget[$last] = ($overwrite) ? $var : $lget[$last]; //set initialized var to new value
							$last = false;
						}
    				}
    			}
    		}
			if(!empty($siteUrlArr['query']))
			{
				$qsget = array();
				parse_str($siteUrlArr['query'],$qsget);
	    		$lget = array_merge($lget, $qsget);
			}
    	}
    	//eof nice url reformating
    	
    	//do not use the link to external $_GET
    	//use a copy of get to preserve the raw get input
    	$get = $lget;
    	
    	$convert = array();
        foreach($get as $key => $value)
        {
            $get[$key] = utf8_decode($value);
        }
        foreach($post as $key => $value)
        {
        	if(substr($key,0,5) == 'cptg_')
        	{
        		$convert[$key] = utf8_decode($value);
        	}
        	else
        	{
	            $post[$key] = utf8_decode($value);
        	}
        }
        foreach($session as $key => $value)
        {
            $session[$key] = utf8_decode($value);
        }
        foreach($files as $key => $value)
        {
            if(!is_array($value))
           	 	$files[$key] = utf8_decode($value);
        }
        foreach($convert as $key => $value)
        {
        	//convert post to get
        	// post[cptg_foo] == bar --> get[bar] = post[foo]
        	if(substr($key,0,5) == 'cptg_')
        	{
        		$get[$value] = $post[substr($key,5)];
        	}
        }
        if(get_magic_quotes_gpc()) 
        {
	        foreach($get as $key => $value)
	        {
	            $get[$key] = stripslashes($value);
	        }
	        foreach($post as $key => $value)
	        {
	            $post[$key] = stripslashes($value);
	        }
	        foreach($session as $key => $value)
	        {
	            $session[$key] = stripslashes($value);
	        }
	        foreach($files as $key => $value)
	        {
	            if(!is_array($value))
	            	$files[$key] = stripslashes($value);
	        }
        }
        $this->get = &$get;
        $this->post = &$post;
        $this->session = &$session;
        $this->uploadfiles = &$files;
        $this->using($this->autoloadClasses);
        return array($this->get, $this->post, $this->session, $this->uploadfiles);
    }
    
    ////////////////////////////////////
    //handle path and setting requests//
    ////////////////////////////////////
    
    function getBambusApplicationDescription($xmlfile)
    {
    	$requests = array('name' => '', 'description' => '', 'icon' => '', 	
    		'priority' => '', 'version' => '', 'purpose' => 'other');
    	$xml = DFileSystem::Load($xmlfile);
    	foreach($requests as $node => $value)
    	{
    		preg_match("/<".$node.">(.*)<\/".$node.">/", $xml, $preg);
			$requests[$node] = (isset($preg[1])) ? $preg[1] : $requests[$node];
    	}

    	preg_match_all("/<tab[\\s]+icon=\"([a-zA-Z0-9-_]+)\">(.*)<\\/tab>/", $xml, $matches);
    	for($i = 0; $i < count($matches[0]); $i++)
    	{
    		$requests['*'.$matches[2][$i]] = $matches[1][$i];
    	}
    	return $requests;
    }
    
    ////////////////////////////////////
    //define available applications//
    ////////////////////////////////////
    function getAvailableApplications()
    {
    	$i = 0;
    	$available = array();
    	chdir(SPath::SYSTEM_APPLICATIONS);
        $Dir = opendir ('./');
        while ($item = readdir ($Dir)) 
        {
			if(is_dir($item) && substr($item,0,1) != '.' && strtolower(DFileSystem::suffix($item)) == 'bap')
            {
                $i++;
                
                $data = $this->getBambusApplicationDescription($item.'/Application.xml');
                $tabs = array();
                foreach ($data as $tab => $icon) 
                {
                	if(substr($tab,0,1) == '*')
                	{
                		$tabs[substr($tab,1)] = $icon;
                	}
                }
                //'name' => '', 'description' => '', 
                //'icon' => '', 'priority' => '', 
                //'version' => '', 'purpose' => 'other'
                $available[$item] = array(
					'purpose' => 'other'
					,'item' => $item
					,'name' => $data['name']
					,'desc' => $data['description']
					,'icon' => $data['icon']
					,'type' => 'application',
					'tabs' => $tabs);
            }        
        }
        closedir($Dir);
        chdir(BAMBUS_CMS_ROOT);
        
        if(!BAMBUS_GRP_ADMINISTRATOR)
        {
        	$keys = array_keys($available);
	        foreach($keys as $id)
	        {
		        $appName = substr($id,0,(strlen(DFileSystem::suffix($id))+1)*-1);
		        $SUsersAndGroups = SUsersAndGroups::alloc()->init();
				if(!$SUsersAndGroups->hasPermission(BAMBUS_USER, $appName))
	        	{
	        		unset($available[$id]);
	        	}
	        
	        }
        }
        return $available;
    }
    
    function selectApplicationFromPool($pool = array())
    {
    	$get = &$this->get;
    	$barCompatibleTabs = array();
    	if(isset($get['editor']) 
        	&& in_array($get['editor'], array_keys($pool))
        	&& file_exists(SPath::SYSTEM_APPLICATIONS.$get['editor'].'/Application.xml'))
        {
         	define('BAMBUS_APPLICATION', 			$get['editor']);
			define('BAMBUS_APPLICATION_DIRECTORY',  SPath::SYSTEM_APPLICATIONS.BAMBUS_APPLICATION.'/');
     		define('BAMBUS_APPLICATION_ICON', 		WIcon::pathFor($pool[$get['editor']]['icon'],'app'));
    		define('BAMBUS_APPLICATION_TITLE', 		SLocalization::get($pool[$get['editor']]['name']));
    		define('BAMBUS_APPLICATION_DESCRIPTION',SLocalization::get($pool[$get['editor']]['desc']));

 			$this->using('Application');
 			$tabs = $this->Application->getXMLPathValueAndAttributes('bambus/tabs/tab');
	    	if(!isset($tabs[0]))
	    	{	//no tabs in xml? - create default "edit"-tab
	    		$tabs[0] = array('edit', array('icon' => 'edit'));
	    	}
	    	$activeTab = $tabs[0];
	    	foreach($tabs as $tab)
	    	{
	    		$barCompatibleTabs[$tab[0]] = array($tab[1]['icon'], SLocalization::get($tab[0]));
	    		if(!empty($get['tab']) && $tab[0] == $get['tab'])
	    			$activeTab = $tab;
	    	}     

        	define('BAMBUS_APPLICATION_TAB', 		$activeTab[0]);
        	define('BAMBUS_APPLICATION_TAB_ICON', 	WIcon::pathFor($activeTab[1]['icon']));
        	define('BAMBUS_APPLICATION_TAB_TITLE', 	SLocalization::get($activeTab[0]));
        }
		else
		{
			$constants = array(
				'BAMBUS_APPLICATION',
				'BAMBUS_APPLICATION_DIRECTORY',
				'BAMBUS_APPLICATION_ICON',
				'BAMBUS_APPLICATION_TITLE',
				'BAMBUS_APPLICATION_DESCRIPTION',
				'BAMBUS_APPLICATION_TAB',
				'BAMBUS_APPLICATION_TAB_ICON',
				'BAMBUS_APPLICATION_TAB_TITLE'
			);
			foreach($constants as $const)
			{
				if(!defined($const))
				{
					define($const, '');
				}
			}
		}
    	return $barCompatibleTabs;
    }
 }//end of class Bambus
?>
