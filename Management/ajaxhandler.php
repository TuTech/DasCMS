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
chdir('..');
require_once('./System/Component/Loader.php');

//go to the cms root
session_start();

//tell the bambus whats going on
@$bambus_user = utf8_decode((!empty($_SESSION['bambus_cms_username'])) ? $_SESSION['bambus_cms_username'] : $_SESSION['uname']);
@$bambus_password = utf8_decode((!empty($_SESSION['bambus_cms_password'])) ? $_SESSION['bambus_cms_password'] : $_SESSION['pwrd']);

$editor = RURL::get('editor');

$appName = substr($editor,0,((strlen(DFileSystem::suffix($editor))+1)*-1));

$SUsersAndGroups = SUsersAndGroups::alloc()->init();

if($SUsersAndGroups->isValidUser($bambus_user, $bambus_password) 
	&& (
		$SUsersAndGroups->isMemberOf($bambus_user, 'Administrator')
		||(
			$SUsersAndGroups->hasPermission($bambus_user, $appName)
			&& $SUsersAndGroups->isMemberOf($bambus_user, 'CMS') 	
		)
	)
  )
{
    //FIXME to be done by users and groups class
    //export the config into an array
	define('BAMBUS_USER', $bambus_user);
	define('BAMBUS_USER_GROUPS', implode('; ', $SUsersAndGroups->listGroupsOfUser(BAMBUS_USER)));
	define('BAMBUS_GRP_ADMINISTRATOR', $SUsersAndGroups->isMemberOf(BAMBUS_USER, 'Administrator'));
	define('BAMBUS_GRP_CREATE', $SUsersAndGroups->isMemberOf(BAMBUS_USER, 'Create') || $SUsersAndGroups->isMemberOf(BAMBUS_USER, 'Administrator'));
	define('BAMBUS_GRP_RENAME', $SUsersAndGroups->isMemberOf(BAMBUS_USER, 'Rename') || $SUsersAndGroups->isMemberOf(BAMBUS_USER, 'Administrator'));
	define('BAMBUS_GRP_DELETE', $SUsersAndGroups->isMemberOf(BAMBUS_USER, 'Delete') || $SUsersAndGroups->isMemberOf(BAMBUS_USER, 'Administrator'));
	define('BAMBUS_GRP_EDIT', $SUsersAndGroups->isMemberOf(BAMBUS_USER, 'Edit') || $SUsersAndGroups->isMemberOf(BAMBUS_USER, 'Administrator'));
	define('BAMBUS_GRP_CMS', $SUsersAndGroups->isMemberOf(BAMBUS_USER, 'CMS') || $SUsersAndGroups->isMemberOf(BAMBUS_USER, 'Administrator'));
	define('BAMBUS_GRP_PHP', $SUsersAndGroups->isMemberOf(BAMBUS_USER, 'PHP') || $SUsersAndGroups->isMemberOf(BAMBUS_USER, 'Administrator'));
	define('BAMBUS_PRIMARY_GROUP', $SUsersAndGroups->getPrimaryGroup(BAMBUS_USER));
    
    //build the shiny bambus menu-bar and check the editor permissions
	define('BAMBUS_APPLICATION_DIRECTORY',  SPath::SYSTEM_APPLICATIONS.BAMBUS_APPLICATION.'/');
    
    //load the desired editor helper
    if(file_exists(SPath::SYSTEM_APPLICATIONS.basename($editor).'/Ajax.php'))
    { 
        include (SPath::SYSTEM_APPLICATIONS.basename($editor).'/Ajax.php');
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