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
$BambusConfigClass = &$Bambus->Configuration;
$BambusConfig = $BambusConfigClass->as_array();

//TODO: remove old env vars
list($get, $post, $session, $files) = array($_GET,$_POST,$_SESSION,$_FILES);

//some permission constants 
define('BAMBUS_USER', ($Bambus->UsersAndGroups->isValidUser($bambus_username, $bambus_password)) ? $bambus_username : '');
define('BAMBUS_USER_GROUPS', BAMBUS_USER != '' ? implode('; ', $Bambus->UsersAndGroups->listGroupsOfUser(BAMBUS_USER)) : '');
define('BAMBUS_GRP_ADMINISTRATOR', $Bambus->UsersAndGroups->isMemberOf(BAMBUS_USER, 'Administrator'));
define('BAMBUS_GRP_CREATE', $Bambus->UsersAndGroups->isMemberOf(BAMBUS_USER, 'Create') || $Bambus->UsersAndGroups->isMemberOf(BAMBUS_USER, 'Administrator'));
define('BAMBUS_GRP_RENAME', $Bambus->UsersAndGroups->isMemberOf(BAMBUS_USER, 'Rename') || $Bambus->UsersAndGroups->isMemberOf(BAMBUS_USER, 'Administrator'));
define('BAMBUS_GRP_DELETE', $Bambus->UsersAndGroups->isMemberOf(BAMBUS_USER, 'Delete') || $Bambus->UsersAndGroups->isMemberOf(BAMBUS_USER, 'Administrator'));
define('BAMBUS_GRP_EDIT', $Bambus->UsersAndGroups->isMemberOf(BAMBUS_USER, 'Edit') || $Bambus->UsersAndGroups->isMemberOf(BAMBUS_USER, 'Administrator'));
define('BAMBUS_GRP_CMS', $Bambus->UsersAndGroups->isMemberOf(BAMBUS_USER, 'CMS') || $Bambus->UsersAndGroups->isMemberOf(BAMBUS_USER, 'Administrator'));
define('BAMBUS_GRP_PHP', $Bambus->UsersAndGroups->isMemberOf(BAMBUS_USER, 'PHP') || $Bambus->UsersAndGroups->isMemberOf(BAMBUS_USER, 'Administrator'));
define('BAMBUS_PRIMARY_GROUP', $Bambus->UsersAndGroups->getPrimaryGroup(BAMBUS_USER));

//set sometemplate keys
$Bambus->Template->setEnv('meta_keywords',$BambusConfigClass->get('meta_keywords'));
$Bambus->Template->setEnv('bambus_version', BAMBUS_VERSION);
$Bambus->Template->setEnv('rssfeeds', '');
$Bambus->Template->setEnv('bambus_my_uri', $Bambus->Linker->myFormURL());
$Bambus->Template->setEnv('logout_href', $Bambus->Linker->createQueryString(array('_destroy_session' => '1')));

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
$Bambus->Template->setEnv('stylesheets', $stylesheetLinks);

//////////////////////////////////////////////////
//display requested content
//////////////////////////////////////////////////





//////////////////////////////
//QSpore
//////////////////////////////

$spores = QSpore::activeSpores();
if(count($spores) > 0)
{
	$Parser = Parser::alloc();
	$Parser->init();
	$Spore = new QSpore($spores[0]);
//	echo 'opening spore: '.$spores[0];
	$content = $Spore->getContent();
	if($content != null && $content instanceof BContent)
	{
		$e = new EContentAccessEvent($Spore, $content);
		if($e->isCanceled())
		{
			$content = MError::alloc()->init()->Open(403);
		}
	}
	echo $Parser->Parse(implode('', file('Content/templates/page.tpl')), $content);
}



/*


//TODO: find better solution
//to invoke the linked navigations parse the body to /dev/null first
$Bambus->Template->parse('header',$BambusConfig);
$Bambus->Template->parse('body',$BambusConfig);
$Bambus->Template->parse('footer',$BambusConfig);
$title = implode(', ',$Bambus->Navigations->getTitles());
$Bambus->Template->setEnv('title', $title);


echo $Bambus->Template->parse('header',$BambusConfig);
echo $Bambus->Template->parse('body',$BambusConfig);

if(!$Bambus->Navigations->needLogin())
{
	//no auth needed or auth ok
    $contents = $Bambus->Navigations->getContentList();
    foreach($contents as $content){
    	if(!$Bambus->Navigations->contentFetched($content))
    	{
		    if(!$Bambus->Navigations->isProgram($content))
		    {
		    	echo $Bambus->Navigations->getContent($content);
		    }
		    else
		    {
		    	$Pages = Pages::alloc();
		    	$Pages->init();
		    	$PageID = $Bambus->Navigations->getContent($content);
		    	if($Pages->exists($PageID))
		    	{
			    	include($Bambus->pathTo('document').$Pages->{$PageID}->FileName);
		    	}
		    }
    	}
    }
}
else
{
    if(!empty($triedToLogin)){
		//timeout for failed logins
    	sleep(10);
    	//TODO: find users language by browser identification
    	$Bambus->using("Translation");
    	$message =  $Bambus->Translation->sayThis("sorry_wrong_username_or_password", 'de');
	}
    $templateLocation = (file_exists($Bambus->pathTo("template")."bambus_login.tpl")) ? "content" : "system";
//    if(!empty($BambusConfig['404redirect']))
//    	$Bambus->setMode('post_form');
    echo @$Bambus->Template->parse('bambus_login', array('bambus_cms_message' => $message), $templateLocation);
//	$Bambus->setMode('site');
}
echo $Bambus->Template->parse('footer',$BambusConfig,'content');
*/
?>