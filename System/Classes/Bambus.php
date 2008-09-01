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
	if($sc == strtoupper($sc) && $className != 'BCMSString')
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
			SNotificationCenter::alloc()->init()->report('alert', 'could_not_load_class'.$className);
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
    var $Template,
    	$Gui,
    	$Configuration,
    	$FileSystem,
    	$UsersAndGroups;

	//include all class-root-level classes
	     
	protected function saveCurrentState()
	{
		if(get_class($this) != 'Bambus')
		{
			$data = sprintf(
				'%64s'
				,'<?php exit(); ?>' //64 chars header 
			).serialize($this); 
			
			$file = BAMBUS_CMS_ROOT.'/Content/configuration/'.get_class($this).'.State.php';
		   	if((file_exists($file) && is_writable($file)) 
				|| (!file_exists($file) && is_writable(dirname($file))))
	    	{
	        	try
	        	{
					$openFile = fopen($file,'w');
		        	flock($openFile, LOCK_EX + LOCK_NB);
	            	$success = fwrite($openFile, $data, strlen($data));
	        	}
	        	catch(Exception $e)
	        	{
	        		printf('<h1>ERROR</h1><!--[%s] Error saving state: %s -->', get_class($this), $e);
	    	    	flock($openFile, LOCK_UN);
	        	}
		    	flock($openFile, LOCK_UN);
	            fclose($openFile);
	    	}
	    	if(empty($success))
	    	{
		    	printf('<h1>ERROR</h1><!--[%s] Error saving state -->', get_class($this));
	    	}
		}
//		echo 'saving state of '.get_class($this);
	}

	protected static function hasPreviousState($ofClass)
	{
		$file = BAMBUS_CMS_ROOT.'/Content/configuration/'.$ofClass.'.State.php';
		return (file_exists($file) && is_readable($file));
	}

	protected static function loadPreviousState($ofClass)
	{
		if(self::hasPreviousState($ofClass))
		{
			$file = BAMBUS_CMS_ROOT.'/Content/configuration/'.$ofClass.'.State.php';
			$header = '';
			$data = '';
			$handle = fopen ($file, "r");
			flock($handle, LOCK_EX + LOCK_NB);
        	if(filesize($file) > 64)
			{
				fseek($handle, 64);
				$data = fread ($handle, filesize($file)-64);
				$data = unserialize($data);
			}
			else
			{
				$data = NULL;
			}
			flock($handle, LOCK_UN);
			fclose ($handle);
			return $data;
		}
		return NULL;
	}

	
    //private
    protected $autoloadClasses = array(
		'BCMSString'
		,'FileSystem'
		,'Configuration'
		,'Template'
		,'Linker'
		,'UsersAndGroups'
	);
	
	protected $managementAutoloadClasses = array(
		'Gui'
		
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
    //define enviornment variables//
    ////////////////////////////////
    
    function Bambus()
    {
		//move to config
        $this->paths = array(
            'document' => 				'./Content/document/',
            'image' => 					'./Content/images/',
            'download' => 				'./Content/download/',
            'navigation' => 			'./Content/navigation/',
            'log' => 					'./Content/logs/',
            'template' => 				'./Content/templates/',
            'backup' => 				'./Content/backup/',
            'css' => 					'./Content/stylesheets/',
            'design' =>					'./Content/stylesheets/',
            'temp' => 					'./Content/temp/',

            'management' => 			'./Management/',
            
            //want a ci matching management-interface? place your stylesheets here!
            'contentSystemOverride' => './Content/systemOverride/',
            
            'system' => 				'./System/',
            'systemApplication' => 		'./System/Applications/',
            'systemInterface' => 		'./System/Interfaces/',
            'systemTemplate' => 		'./System/Templates/',
            'systemImage' => 			'./System/Images/',
            'systemSmallMimeImage' => 	'./System/Icons/22x22/mimetypes/',
            'systemMediumMimeImage' => 	'./System/Icons/32x32/mimetypes/',
            'systemIcon' => 			'./System/Icons/',
            'systemClientScript' => 	'./System/ClientScripts/',
            'systemClientDataScript' => './System/ClientData/Scripts/',
            'systemClientDataStylesheet' =>'./System/ClientData/Stylesheets/',
        );
        $this->files = array(
            'configuration' => 			'./Content/configuration/system.php',
            'userList' => 				'./Content/configuration/users.php',
            'groupList' => 				'./Content/configuration/groups.php',
            'documentIndex' => 			'./Content/document/index.php',
            'css' => 					'./Content/stylesheets/<file>.css',
            'template' => 				'./Content/templates/<file>.tpl',
            'log' => 					'./Content/logs/<file>.log',
			'accessLog' => 				'./Content/logs/access.log',
            'changeLog' => 				'./Content/logs/change.log',
            'systemTemplate' => 		'./System/Templates/<file>.tpl'
        );
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
		$classNames = $this->isAnArray($classNames);
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
    	$this->using(array('FileSystem', 'Configuration', 'Linker'));
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


    ////////////////////
    //create link urls//
    ////////////////////
    
    //TODO: still in use?
    function createQueryString($changes = array(),$forAppNavigator = false)
    {
        if($forAppNavigator)
        {
        	$tget = $this->Linker->get;
        	
            $this->Linker->get = array('editor' => isset($this->Linker->get['editor']) ? $this->Linker->get['editor'] : '');
            $qs = $this->Linker->createQueryString($changes);
            $this->Linker->get = $tget;
	        return $qs;
        }
        else
        	return$this->Linker->createQueryString($changes);
    }
    
    ////////////////////////////////////
    //handle path and setting requests//
    ////////////////////////////////////
    
    function pathTo($opt)
    {
        $paths = &$this->paths;
        if(isset($paths[$opt]))
        {
            return $paths[$opt];
        }
        else
        {
	        return '';
        }
    }
    
    function pathToFile($opt, $filename = '')
    {
        return str_replace('<file>',$filename,$this->files[$opt]);
    }  
    
    ////////////////////
	//check user login//
	////////////////////
	
    function login()
    {
        $get = &$this->get;
        $post = &$this->post;
        $session = &$this->session;
        if(!empty($post['bambus_cms_login']))
        {
            if($this->User->verify($post['username'],$post['password']))
            {
                $session['user'] = $post['username'];
                $session['password'] = $post['password'];
            }
        }
        $session['user'] = empty($session['user']) ? '' : $session['user'];
        $session['password'] = empty($session['password']) ? '' : $session['password'];
        $get['editor'] = empty($get['editor']) ? '' : $get['editor'];
        return $this->User->login($session['user'], $session['password']);
    }

 /**********------> App class*************/

 	////////////////////////////////////////
	//load application description of BAPs//
    ////////////////////////////////////////
    
    function getBambusApplicationDescription($xmlfile)
    {
    	$requests = array('name' => '', 'description' => '', 'icon' => '', 	
    		'priority' => '', 'version' => '', 'purpose' => 'other');
    	$xml = $this->FileSystem->read($xmlfile);
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
    	$this->FileSystem->changeDir('systemApplication');
        $Dir = opendir ('./');
        while ($item = readdir ($Dir)) 
        {
			if(is_dir($item) && substr($item,0,1) != '.' && strtolower($this->suffix($item)) == 'bap')
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
        $this->FileSystem->returnToRootDir();
        
        if(!BAMBUS_GRP_ADMINISTRATOR)
        {
        	$keys = array_keys($available);
	        foreach($keys as $id)
	        {
		        $appName = substr($id,0,(strlen($this->suffix($id))+1)*-1);
				if(!$this->UsersAndGroups->hasPermission(BAMBUS_USER, $appName))
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
        	&& file_exists($this->pathTo('systemApplication').$get['editor'].'/Application.xml'))
        {
         	define('BAMBUS_APPLICATION', 			$get['editor']);
			define('BAMBUS_APPLICATION_DIRECTORY',  $this->pathTo('systemApplication').BAMBUS_APPLICATION.'/');
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
    
    

    ////////////////////////////////////
    //create the application navigator//
    ////////////////////////////////////
    function applicationNavigator()
    {
    	$get = &$this->get;
    	$editors = array();
        $outputStarted = false;
        $i = 0;
        //get all editors
    	$this->FileSystem->changeDir('systemApplication');
        $Dir = opendir ('./');
        while ($item = readdir ($Dir)) 
        {
			if(is_dir($item) && substr($item,0,1) != '.' && strtolower($this->suffix($item)) == 'bap')
            {
                $i++;
                list($name, $description, $icon, $pri, $version, $purpose) = $this->getBambusApplicationDescription($item.'/Application.xml');
                $caption = SLocalization::get($name);
                $available[$caption.'_'.$i] = array('purpose' => $purpose, 'item' => $item,'name' => $name,'desc' => $description,'icon' => $icon, 'type' => 'application');
            }        
        }
        closedir($Dir);
        $this->FileSystem->returnToRootDir();
        //sort by translated app name
        ksort($available);
        //check editor permissions
        $appKeys = array_keys($available);
        $accessable = array();
        $appinfo = array();
        for ($i = 0; $i < count($appKeys); $i++) 
        {
        	$application = $available[$appKeys[$i]];
			$appName = substr($application['item'],0,((strlen($this->suffix($application['item']))+1)*-1));
			if(!BAMBUS_GRP_ADMINISTRATOR && !$this->UsersAndGroups->hasPermission(BAMBUS_USER, $appName))
			{
				unset($available[$appKeys[$i]]);
			}
			else
			{
				$accessable[] = $application['item'];
				$appinfo[$application['item']] = $application;
			}
		}
        //select active application
        //or EXIT if there are no apps
        if(count($accessable) == 0) 
        	return '';
        
        $userPriApp = $this->UsersAndGroups->getMyApplicationPreference('', 'PrimaryApplication');
        if(isset($get['editor']) && in_array($get['editor'], $accessable))
        {
        	$editor = $get['editor'];
        	$appas = false;
        }
        elseif(count($accessable) == 1)
        {
        	$editor = $accessable[0];
        	$appas = false;
        }
        elseif(!empty($userPriApp)  && in_array($userPriApp, $accessable))
        {
         	$editor = $userPriApp;
         	$appas = false;
        }
        else
        {
         	$appas = true;
        }
        if(!$appas)
	        $smallicon = WIcon::pathFor($appinfo[$editor]['icon']);
     	define('BAMBUS_APPLICATION_AUTOSELECT', $appas);
     	define('BAMBUS_APPLICATION', $appas ? '' : $editor);
     	define('BAMBUS_APPLICATION_ICON', $appas ? '' : $smallicon);
    	define('BAMBUS_APPLICATION_TITLE', $appas ? '' : SLocalization::get($appinfo[$editor]['name']));
    	define('BAMBUS_APPLICATION_DESCRIPTION', $appas ? '' : SLocalization::get($appinfo[$editor]['desc']));
        $outputString = '';
        foreach($accessable as $app)
        {
         	$micon = new WIcon($appinfo[$app]['icon'], '', WIcon::LARGE, 'app');
        	$outputString .= sprintf(
						"\t<a class=\"%sbambus_type_%s\" href=\"%s\">%s\n\t\t".
							"<span class=\"ApplicationTitle\">%s</span>\n\t\t".
							"<span class=\"ApplicationDescription\">%s</span>\n\t".
							"</a>\n"
						,!$appas && $app ==  $editor ? 'active ' : ''
						,$appinfo[$app]['type']
						,$this->createQueryString(array('editor' => $appinfo[$app]['item']),true)
						,$micon
						,SLocalization::get($appinfo[$app]['name'])
						,SLocalization::get($appinfo[$app]['desc'])
            		);
            $micon = new WIcon($appinfo[$app]['icon'], SLocalization::get($appinfo[$app]['desc']), WIcon::LARGE, 'app'); 
            $tabs = '';
            foreach ($appinfo[$app]['tabs'] as $tab => $icon) 
        	{
        		$tabs .= sprintf(
        			'<a class="%sapplication_tab"  href=\"%s\">%s%s</a>'
        			,$_GET['tab'] == $tab
        			,$this->createQueryString(array('editor' => $appinfo[$app]['item'], 'tab' => $tab),true)
        			,new WIcon($icon, '', WIcon::EXTRA_SMALL)
        		);
        	}
            $outputString .= sprintf(
						"\t<div class=\"%sbambus_type_application\">".
							"%s\n\t\t".
							"<h2 class=\"ApplicationTitle\">%s</h2>\n\t\t".
        					"%s".
							"</a>\n"
						,!$appas && $app ==  $editor ? 'active ' : ''
//						,$this->createQueryString(array('editor' => $appinfo[$app]['item']),true)
						,$micon
						,SLocalization::get($appinfo[$app]['name'])
						,$tabs
            		);
        }
        return $outputString;
    }
/********** EOF ----> App class*************/
   
/****************************************************************
 * TODO: MV TO EXTERNAL LIB CLASS 
 * */

    public static function suffix($of)
    {
        $tmp = explode('.',strtolower($of));
        return array_pop($tmp);
    }
    
    public static function returnBytes($val) 
    {
	   $val = strtolower(trim($val));
	   if(substr($val, -1) == 'b')
	   {
	   		$last = substr($val, -2, 1);
	   		$val =  substr($val, 0, -2);
	   }
	   else
	   {
	   		$last = substr($val, -1);
	   		$val =  substr($val, 0, -1);
	   }
	   switch($last) 
	   {
	       case 'y':
	           $val *= 1024;
	       case 'z':
	           $val *= 1024;
	       case 'e':
	           $val *= 1024;
	       case 'p':
	           $val *= 1024;
	       case 't':
	           $val *= 1024;
	       case 'g':
	           $val *= 1024;
	       case 'm':
	           $val *= 1024;
	       case 'k':
	           $val *= 1024;
	   }
	   return $val;
	}
	
    public static function formatSize($bytes)
    {
        $units = array('B','KB','MB','GB','TB','PB','EB','ZB','YB');
        $loops = 0;
        while($bytes >= 1024)
        {
            $loops++;
            $bytes /= 1024;
        }        
        return round($bytes,2).$units[$loops];
    }
    	
    public static function isAnArray($whatever)
	{
		return (is_array($whatever)) ? $whatever : array($whatever);
	}
 }//end of class Bambus
?>
