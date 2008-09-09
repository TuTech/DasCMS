<?php 
/**
 * @package Bambus
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @license GNU General Public License 3
 * @since 09.09.2008
 */

/**
 * Class Autoloader (PHP magic function)
 * @param string $className 
 */
function __autoload($className)
{
	$ComponentMap = array(
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
	$fc = substr($className,0,1); 
	$file = sprintf("./System/Component/%s/%s.php", $ComponentMap[$fc], $className);
	if(array_key_exists($fc, $ComponentMap) && file_exists($file))
	{
		include_once($file);
	}
	else
	{
	    throw new Exception('class not found "'.$className.'"');
	}
}
if(!defined('BAMBUS_VERSION'))
{
    define ('BAMBUS_VERSION', '0.21.0-DEV20080909');
        
    if(!defined('BAMBUS_CMS_ROOTDIR'))
        define('BAMBUS_CMS_ROOTDIR',getcwd());
    
    if(!defined('BAMBUS_VERSION_NAME'))
        define ('BAMBUS_VERSION_NAME', 'Bambus CMS '.constant('BAMBUS_VERSION'));
    
    if(!defined('BAMBUS_EXEC_START'))
        define ('BAMBUS_EXEC_START', microtime(true));
    
    date_default_timezone_set('Europe/Berlin');
    setlocale(LC_ALL, 'de_DE@euro', 'de_DE', 'de', 'ge');
    error_reporting(E_ALL|E_STRICT);
}
?>
