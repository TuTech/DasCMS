<?php /*******************************************
* Bambus CMS 
* Created:     12.06.2006
* License:     GNU GPL Version 2 or later (http://www.gnu.org/copyleft/gpl.html)
* Copyright:   Lutz Selke/TuTech Innovation GmbH 
* Description: Handles all AJAX requests and redirects them to the Application AJAX-handler
* Version      0.9.0
************************************************/
header('Content-Type: text/html; charset=utf-8');
setlocale (LC_ALL, 'de_DE');
//load the mighty bambus
define('BAMBUS_ACCESS_TYPE', 'management');
define('BAMBUS_CMS_DEFAULT_LANGUAGE', 'de');
chdir('..');
require_once('./System/Classes/Bambus.php');
$Bambus = new Bambus();
//$Bambus->setMode('editor');

//go to the cms root
session_start();

//tell the bambus whats going on
list($get, $post, $session, $uploadfiles) = $Bambus->initialize($_GET,$_POST,$_SESSION,$_FILES);
@$bambus_user = utf8_decode((!empty($_SESSION['bambus_cms_username'])) ? $_SESSION['bambus_cms_username'] : $_SESSION['uname']);
@$bambus_password = utf8_decode((!empty($_SESSION['bambus_cms_password'])) ? $_SESSION['bambus_cms_password'] : $_SESSION['pwrd']);

$get['editor'] = isset($get['editor']) ? $get['editor'] : '';
$appName = substr($get['editor'],0,((strlen($Bambus->suffix($get['editor']))+1)*-1));
if($Bambus->UsersAndGroups->isValidUser($bambus_user, $bambus_password) 
	&& (
		$Bambus->UsersAndGroups->isMemberOf($bambus_user, 'Administrator')
		||(
			$Bambus->UsersAndGroups->hasPermission($bambus_user, $appName)
			&& $Bambus->UsersAndGroups->isMemberOf($bambus_user, 'CMS') 	
		)
	)
  )
{
    //export the config into an array
	define('BAMBUS_USER', $bambus_user);
	define('BAMBUS_USER_GROUPS', implode('; ', $Bambus->UsersAndGroups->listGroupsOfUser(BAMBUS_USER)));
	define('BAMBUS_GRP_ADMINISTRATOR', $Bambus->UsersAndGroups->isMemberOf(BAMBUS_USER, 'Administrator'));
	define('BAMBUS_GRP_CREATE', $Bambus->UsersAndGroups->isMemberOf(BAMBUS_USER, 'Create') || $Bambus->UsersAndGroups->isMemberOf(BAMBUS_USER, 'Administrator'));
	define('BAMBUS_GRP_RENAME', $Bambus->UsersAndGroups->isMemberOf(BAMBUS_USER, 'Rename') || $Bambus->UsersAndGroups->isMemberOf(BAMBUS_USER, 'Administrator'));
	define('BAMBUS_GRP_DELETE', $Bambus->UsersAndGroups->isMemberOf(BAMBUS_USER, 'Delete') || $Bambus->UsersAndGroups->isMemberOf(BAMBUS_USER, 'Administrator'));
	define('BAMBUS_GRP_EDIT', $Bambus->UsersAndGroups->isMemberOf(BAMBUS_USER, 'Edit') || $Bambus->UsersAndGroups->isMemberOf(BAMBUS_USER, 'Administrator'));
	define('BAMBUS_GRP_CMS', $Bambus->UsersAndGroups->isMemberOf(BAMBUS_USER, 'CMS') || $Bambus->UsersAndGroups->isMemberOf(BAMBUS_USER, 'Administrator'));
	define('BAMBUS_GRP_PHP', $Bambus->UsersAndGroups->isMemberOf(BAMBUS_USER, 'PHP') || $Bambus->UsersAndGroups->isMemberOf(BAMBUS_USER, 'Administrator'));
	define('BAMBUS_PRIMARY_GROUP', $Bambus->UsersAndGroups->getPrimaryGroup(BAMBUS_USER));
    
    //build the shiny bambus menu-bar and check the editor permissions
    $Bambus->Template->setEnv('appNavigator', $Bambus->applicationNavigator());
	define('BAMBUS_APPLICATION_DIRECTORY',  $Bambus->pathTo('systemApplication').BAMBUS_APPLICATION.'/');
    $config = $Bambus->Template->exportEnv();
    
    //load the desired editor helper
    if(file_exists($Bambus->pathTo('systemApplication').basename($get['editor']).'/Ajax.php'))
    { 
        include ($Bambus->pathTo('systemApplication').basename($get['editor']).'/Ajax.php');
    }
    else
    {
        die("No bambus for you, hungry Panda! - No Ajax controller");
    }
}
else
{
    die("No bambus for you, hungry Panda!");
}
?>