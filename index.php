<?php
/************************************************
* Bambus CMS 
* Created:     21. Sep 06
* Last change: 03. Jul 07
* License:     GNU GPL Version 2 or later (http://www.gnu.org/copyleft/gpl.html)
* Copyright:   Lutz Selke/TuTech Innovation GmbH 
* Description: Bambus Website generator
* Version:     0.13.0
************************************************/
error_reporting(E_ALL|E_NOTICE|E_STRICT);

setlocale (LC_ALL, 'de_DE');
//Bambus uses UTF-8 and sessions will allways be active... TODO: check if sessions are necessary
header('Content-Type: text/html; charset=utf-8');
session_start();

//now we have a running session we might want to kill it
if(isset($_GET['_destroy_session']) || isset($_GET['_bcms_logout']) || isset($_GET['_bambus_logout']))
{
	$_SESSION['bambus_cms_username'] = '';
	$_SESSION['bambus_cms_password'] = '';
    session_destroy();
}
//or create login differently
if(isset($_POST['bambus_cms_login']) && isset($_POST['bambus_cms_username']) && isset($_POST['bambus_cms_password'])){
    $triedToLogin = true;
    $_SESSION['bambus_cms_username'] = $_POST['bambus_cms_username'];
    $_SESSION['bambus_cms_password'] = $_POST['bambus_cms_password'];
}
$bambus_username = utf8_decode(isset($_SESSION['bambus_cms_username'])  ? $_SESSION['bambus_cms_username'] : '');
$bambus_password = utf8_decode(isset($_SESSION['bambus_cms_password'])  ? $_SESSION['bambus_cms_password'] : '');

//load the bambus main class and initialize it
require_once('./System/Classes/Bambus.php');
$Bambus = new Bambus('site');

//tell bambus whats going on
$Bambus->initialize($_GET,$_POST,$_SESSION,$_FILES, $_SERVER['REQUEST_URI']);
$BambusConfig = LConfiguration::as_array();

$SUsersAndGroups = SUsersAndGroups::alloc()->init();


//TODO: remove old env vars
list($get, $post, $session, $files) = array($_GET,$_POST,$_SESSION,$_FILES);

//some permission constants 
define('BAMBUS_USER', ($SUsersAndGroups->isValidUser($bambus_username, $bambus_password)) ? $bambus_username : '');
define('BAMBUS_USER_GROUPS', BAMBUS_USER != '' ? implode('; ', $SUsersAndGroups->listGroupsOfUser(BAMBUS_USER)) : '');
define('BAMBUS_GRP_ADMINISTRATOR', $SUsersAndGroups->isMemberOf(BAMBUS_USER, 'Administrator'));
define('BAMBUS_GRP_CREATE', $SUsersAndGroups->isMemberOf(BAMBUS_USER, 'Create') || $SUsersAndGroups->isMemberOf(BAMBUS_USER, 'Administrator'));
define('BAMBUS_GRP_RENAME', $SUsersAndGroups->isMemberOf(BAMBUS_USER, 'Rename') || $SUsersAndGroups->isMemberOf(BAMBUS_USER, 'Administrator'));
define('BAMBUS_GRP_DELETE', $SUsersAndGroups->isMemberOf(BAMBUS_USER, 'Delete') || $SUsersAndGroups->isMemberOf(BAMBUS_USER, 'Administrator'));
define('BAMBUS_GRP_EDIT', $SUsersAndGroups->isMemberOf(BAMBUS_USER, 'Edit') || $SUsersAndGroups->isMemberOf(BAMBUS_USER, 'Administrator'));
define('BAMBUS_GRP_CMS', $SUsersAndGroups->isMemberOf(BAMBUS_USER, 'CMS') || $SUsersAndGroups->isMemberOf(BAMBUS_USER, 'Administrator'));
define('BAMBUS_GRP_PHP', $SUsersAndGroups->isMemberOf(BAMBUS_USER, 'PHP') || $SUsersAndGroups->isMemberOf(BAMBUS_USER, 'Administrator'));
define('BAMBUS_PRIMARY_GROUP', $SUsersAndGroups->getPrimaryGroup(BAMBUS_USER));

//set sometemplate keys
WTemplate::globalSet('meta_keywords',LConfiguration::get('meta_keywords'));
WTemplate::globalSet('bambus_version', BAMBUS_VERSION);
WTemplate::globalSet('rssfeeds', '');
WTemplate::globalSet('bambus_my_uri', SLink::buildURL());
WTemplate::globalSet('logout_href', SLink::link(array('_destroy_session' => '1')));

//any stylesheets assigned to this document?
$stylesheetLinks = '<link rel="stylesheet" href="./Content/stylesheets/default.css" type="text/css" media="all" />';
if(!empty($stylesheets)){
    $styles = unserialize($stylesheets);
    foreach($styles as $style){
        if(!empty($style['ieopts'])) $stylesheetLinks .= "\n<!--[if ".$style['ieopts']."]>";
        $stylesheetLinks .= "\n".'<link rel="stylesheet" href="./Content/stylesheets/'.$style['name'].'.css" type="text/css" media="'.$style['media'].'" />';
        if(!empty($style['ieopts'])) $stylesheetLinks .= "\n<![endif]-->\n";
    }
}
WTemplate::globalSet('stylesheets', $stylesheetLinks);


//////////////////////////////
//QSpore
//////////////////////////////

$spores = QSpore::activeSpores();
if(count($spores) > 0)
{
	$Parser = SParser::alloc()->init();
	$Spore = new QSpore($spores[0]);
	$content = $Spore->getContent();
	if($content != null && $content instanceof BContent)
	{
		$e = new EContentAccessEvent($Spore, $content);
		if($e->isCanceled())
		{
			$content = MError::alloc()->init()->Open(403);
		}
	}
	echo $Parser->parse(implode('', file('Content/templates/page.tpl')), $content);
}
?>