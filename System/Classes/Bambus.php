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
			'R' => 'Request',
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
        }
    }   
 }//end of class Bambus
?>
