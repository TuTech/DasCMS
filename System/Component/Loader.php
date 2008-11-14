<?php 
/**
 * @package Bambus
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @license GNU General Public License 3
 * @since 09.09.2008
 */
if(!defined('ERROR_TEMPLATE'))
{
    define('ERROR_TEMPLATE', '<div style="font-family:sans-serif;border:1px solid #a40000;">
        <div style="border:1px solid #cc0000;z-index:1000000;padding:10px;background:#a40000;color:white;">
            <h1 style="border-bottom:1px solid #cc0000;font-size:16px;">%s <code>%d</code> in "%s" at line %d</h1>
            <p>%s</p>
            <p><pre>%s</pre></p>
			<p>CWD: %s</p>
        </div>
    </div>');
}
function EX_Handler(Exception $e)
{
    printf(ERROR_TEMPLATE
        , get_class($e)
        , $e->getCode()
        , $e->getFile()
        , $e->getLine()
        , $e->getMessage()
        , $e->getTraceAsString()
        ,getcwd());
    exit(1);
}

function ER_Handler( $errno ,  $errstr ,  $errfile ,  $errline ,  $errcontext  )
{
    SNotificationCenter::report('warning',
        sprintf('%s %d in %s at %s: %s'
            , 'Error'
            , $errno
            , $errfile
            , $errline
            , $errstr
            , $context
            ,getcwd()));
    ob_start();
    print_r($errcontext);
    $context = ob_get_contents();
    ob_end_clean();
    printf(ERROR_TEMPLATE
        , 'Error'
        , $errno
        , $errfile
        , $errline
        , $errstr
        , $context
        ,getcwd());
    
}
//set_error_handler('ER_Handler');
set_exception_handler('EX_Handler');

/**
 * Class Autoloader (PHP magic function)
 * @param string $className 
 */
function __autoload($className)
{
    $cwd = getcwd();
    chdir(BAMBUS_CMS_ROOTDIR);
	$ComponentMap = array(
		'A' => 'AppController',
		'B' => 'Base',
		'C' => 'Content',
		'D' => 'Driver',
		'E' => 'Event',
		'F' => 'Feed',
		'H' => 'EventHandler',
		'I' => 'Interface',
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
	            return;
	        }
	    }
	    
	    $file = sprintf("./System/Component/%s/%s.php", $ComponentMap[$fc], $className);
		if(file_exists($file))
		{
		    include_once($file);
		}
		else
		{
		    die('File not found: '.  $file.' in '.getcwd());
		}
	}
	else
	{
	    throw new Exception('class not found "'.$className.'"');
	}
	chdir($cwd);
}
if(!defined('BAMBUS_VERSION'))
    define ('BAMBUS_VERSION', 'Bambus CMS 0.92.0.20081114-ALPHA');
        
if(!defined('BAMBUS_CMS_ROOTDIR'))
    define('BAMBUS_CMS_ROOTDIR',getcwd());

if(!defined('BAMBUS_EXEC_START'))
    define ('BAMBUS_EXEC_START', microtime(true));

date_default_timezone_set('Europe/Berlin');
setlocale(LC_ALL, 'de_DE@euro', 'de_DE', 'de', 'ge');
error_reporting(E_ALL|E_STRICT);
?>
