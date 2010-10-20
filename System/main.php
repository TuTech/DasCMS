<?php
/**
 * @package Bambus
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @license GNU General Public License 3
 * @since 2010-05-06
 */

chdir(dirname(__FILE__));
chdir('..');

if(!defined('CMS_START_TIME'))
    define('CMS_START_TIME', microtime(true));

if(!defined('BAMBUS_VERSION_NUMBER'))
    define ('BAMBUS_VERSION_NUMBER', '0.98.20100921');

if(!defined('BAMBUS_VERSION_NAME'))
    define ('BAMBUS_VERSION_NAME', 'Bambus CMS');

if(!defined('BAMBUS_VERSION'))
    define ('BAMBUS_VERSION', BAMBUS_VERSION_NAME.' '.BAMBUS_VERSION_NUMBER);

if(!defined('BAMBUS_CMS_ROOTDIR'))
    define('BAMBUS_CMS_ROOTDIR',getcwd());
    
if(!defined('CMS_ROOT'))
    define('CMS_ROOT',constant('BAMBUS_CMS_ROOTDIR'));

if(!defined('CMS_TEMP')){
	$tmpD = constant('CMS_ROOT').'/Content/temp';
	if(!file_exists($tmpD) || !is_dir($tmpD) || !is_writable($tmpD)){
		$tmpD = constant('CMS_ROOT');
	}
	define('CMS_TEMP',$tmpD);
	unset ($tmpD);
}

if(!defined('CMS_CLASS_CACHE_PATH'))
    define('CMS_CLASS_CACHE_PATH',constant('BAMBUS_CMS_ROOTDIR').'/Content/ClassCache/');
    
if(!defined('CMS_CLASS_PATH'))
    define('CMS_CLASS_PATH',constant('BAMBUS_CMS_ROOTDIR').'/System/Components/');

if(!defined('BAMBUS_EXEC_START'))
    define ('BAMBUS_EXEC_START', microtime(true));

if(!defined('CHARSET'))
    define ('CHARSET', 'UTF-8');


//core classes
require_once constant('CMS_CLASS_PATH').'Core.lib/Core.php';
require_once constant('CMS_CLASS_PATH').'Core.lib/Settings.php';

//class loader
function __autoload($class){
	//try the fast class loader
	$file = Core::getClassCachePath($class);
	
	//if it didn't work try to find the class in the Core.lib (classes not indexed?)
	if(!file_exists($file)){
		$file = sprintf('%s/Core.lib/%s.php', constant('CMS_CLASS_PATH'), str_replace('_', '/', $class));
	}

	//class not found: fail
	if(!file_exists($file)){
		throw new Exception(sprintf('requested class not found "%s"', $class));
	}
	require_once($file);
}

//error handling
if(Core::classExists('SErrorAndExceptionHandler')
		&& class_exists('SErrorAndExceptionHandler', true))
{
    set_error_handler('SErrorAndExceptionHandler::errorHandler');
    set_exception_handler('SErrorAndExceptionHandler::exceptionHandler');
}
//Locale settings
date_default_timezone_set(Core::Settings()->getOrDefault('timezone', 'UTC'));
setlocale(LC_ALL, Core::Settings()->getOrDefault('locale', 'de-DE'));