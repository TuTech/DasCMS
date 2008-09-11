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

RSession::start();
PAuthentication::required();

$editor = RURL::get('editor');

$appName = substr($editor,0,((strlen(DFileSystem::suffix($editor))+1)*-1));

$SUsersAndGroups = SUsersAndGroups::alloc()->init();

if(
	$SUsersAndGroups->isMemberOf(PAuthentication::getUserID(), 'Administrator')
	||(
		$SUsersAndGroups->hasPermission(PAuthentication::getUserID(), $appName)
		&& $SUsersAndGroups->isMemberOf(PAuthentication::getUserID(), 'CMS') 	
	)
  )
{
    //FIXME to be done by users and groups class
    //export the config into an array
	define('BAMBUS_USER_GROUPS', implode('; ', $SUsersAndGroups->listGroupsOfUser(PAuthentication::getUserID())));
	define('BAMBUS_GRP_ADMINISTRATOR', $SUsersAndGroups->isMemberOf(PAuthentication::getUserID(), 'Administrator'));
	define('BAMBUS_GRP_CREATE', $SUsersAndGroups->isMemberOf(PAuthentication::getUserID(), 'Create') || $SUsersAndGroups->isMemberOf(PAuthentication::getUserID(), 'Administrator'));
	define('BAMBUS_GRP_RENAME', $SUsersAndGroups->isMemberOf(PAuthentication::getUserID(), 'Rename') || $SUsersAndGroups->isMemberOf(PAuthentication::getUserID(), 'Administrator'));
	define('BAMBUS_GRP_DELETE', $SUsersAndGroups->isMemberOf(PAuthentication::getUserID(), 'Delete') || $SUsersAndGroups->isMemberOf(PAuthentication::getUserID(), 'Administrator'));
	define('BAMBUS_GRP_EDIT', $SUsersAndGroups->isMemberOf(PAuthentication::getUserID(), 'Edit') || $SUsersAndGroups->isMemberOf(PAuthentication::getUserID(), 'Administrator'));
	define('BAMBUS_GRP_CMS', $SUsersAndGroups->isMemberOf(PAuthentication::getUserID(), 'CMS') || $SUsersAndGroups->isMemberOf(PAuthentication::getUserID(), 'Administrator'));
	define('BAMBUS_PRIMARY_GROUP', $SUsersAndGroups->getPrimaryGroup(PAuthentication::getUserID()));
    
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