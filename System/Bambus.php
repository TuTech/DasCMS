<?php
/**
 * @package Bambus
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 17.12.2007
 * @license GNU General Public License 3
 * @ignore 
 */
/**
 * 
 */
error_reporting(E_ALL|E_STRICT);
setlocale(LC_ALL, 'de_DE@euro', 'de_DE', 'de', 'ge');
define('PHP_PATH_SEPARATOR', '/');
define('BAMBUS_CMS_VERSION_ID', 'Bambus CMS 0.20.20071203 Aurea');
define('BAMBUS_CMS_ROOTDIR',getcwd());
date_default_timezone_set('Europe/Berlin');
define('BAMBUS_USER','HoggenFarker');
function __autoload($class)
{
//	echo "\n<!--[autoload] ",$class," -->\n";
	$Components = array(
//		'A' => 'AppController',
		'B' => 'Base',
		'C' => 'Content',
		'D' => 'Driver',
		'E' => 'Event',
		'H' => 'EventHandler',
		'I' => 'Interface',
		'M' => 'Manager',
		'N' => 'Navigator',
		'Q' => 'Query',
		'S' => 'System',
		'W' => 'Widget',
		'X' => 'Exception'
	);
	
	$fc = substr($class,0,1); //first char
	$sc = substr($class,1,1); //second char
	if($sc == strtolower($sc))//valid class name begins with to uppercase chars 
	{						  //use the content class as default
		$fc = 'M';
		$class = 'M'.$class;
	}
	$file = sprintf("./System/Component/%s/%s.php", $Components[$fc], $class);
	if(array_key_exists($fc, $Components) && file_exists($file))
	{
		include_once($file);
	}
	else
	{
		return false;
	}
}
?>