<?php 
/**
 * @package Bambus
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @license GNU General Public License 3
 * @since 2008-09-09
 */
if(!defined('CMS_START_TIME'))
    define('CMS_START_TIME', microtime(true));

    if(!defined('BAMBUS_VERSION_NUMBER'))
    define ('BAMBUS_VERSION_NUMBER', '0.95.0.20090219');
        
if(!defined('BAMBUS_VERSION_NAME'))
    define ('BAMBUS_VERSION_NAME', 'Bambus CMS');
        
if(!defined('BAMBUS_VERSION'))
    define ('BAMBUS_VERSION', BAMBUS_VERSION_NAME.' '.BAMBUS_VERSION_NUMBER);
        
if(!defined('BAMBUS_CMS_ROOTDIR'))
    define('BAMBUS_CMS_ROOTDIR',getcwd());

if(!defined('BAMBUS_EXEC_START'))
    define ('BAMBUS_EXEC_START', microtime(true));

function __autoload($class)
{
    $cwd = getcwd();
    chdir(constant('BAMBUS_CMS_ROOTDIR'));
    if(strpos($class, '_') !== false)
    {
        object_autoload($class);
    }
    if(!class_exists($class, false) && !interface_exists($class, false))
    {
        component_autoload($class);
    }
    chdir($cwd);
}
function object_autoload($class)
{
    $pfx = '';
    $fileName = '';
    $path = 'Object';
    if(substr($class, 0, 1) == '_')
    {
        $pfx = '_';
        $class = substr($class, 1);
    }
    $tree = explode('_', $class);
    if(count($tree))
    {
        $fileName = array_pop($tree);
        if($pfx == '_')
        {
            array_push($tree, $fileName);
        }
    }
    array_unshift($tree, 'Object');
    $path = implode('/', $tree);
    $file = sprintf('./System/%s/%s%s.php', $path, $pfx, $fileName);
    if(file_exists($file))
    {
        include_once($file);
    }
}

/**
 * Class Autoloader (PHP magic function)
 * @param string $className 
 */
function component_autoload($className)
{
    
	$ComponentMap = array(
		'A' => 'AppController',
		'B' => 'Base',
		'C' => 'Content',
		'D' => 'Driver',
		'E' => 'Event',
		'H' => 'EventHandler',
		'I' => 'Interface',
		'J' => 'Job',
        'L' => 'Legacy',
		'N' => 'Navigator',
		'P' => 'Provider',
		'Q' => 'Query',
		'R' => 'Request',
        'S' => 'System',
		'T' => 'TemplateEngine',
		'U' => 'Plugin',
        'V' => 'View',
        'W' => 'Widget',
		'X' => 'Exception'
	);
	$fc = substr($className,0,1); 
	
	if(array_key_exists($fc, $ComponentMap))
	{
	    //load queries for current database-engine
	    if($fc == 'Q')
	    {
	        $dbEngine = LConfiguration::get('db_engine');
	        $file = sprintf("./System/Component/%s/Q%s_%s.php", $ComponentMap[$fc], $dbEngine, substr($className,1));
	        if(file_exists($file))
	        {
	            include_once($file);
	        }
	    }
	    
	    $file = sprintf("./System/Component/%s/%s.php", $ComponentMap[$fc], $className);
		if(file_exists($file))
		{
		    include_once($file);
		}
	}
}

if(class_exists('SErrorAndExceptionHandler', true))
{
    set_error_handler('SErrorAndExceptionHandler::errorHandler');
    set_exception_handler('SErrorAndExceptionHandler::exceptionHandler');
}

date_default_timezone_set(LConfiguration::get('timezone'));
setlocale(LC_ALL, LConfiguration::get('locale'));
error_reporting(E_ALL|E_STRICT);
?>
