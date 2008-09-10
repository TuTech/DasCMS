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
//Bambus uses UTF-8 and sessions will allways be active... TODO: check if sessions are necessary
header('Content-Type: text/html; charset=utf-8');
require_once('./System/Component/Loader.php');




$err = <<<ERR
    <div style="font-family:sans-serif;border:1px solid #a40000;">
        <div style="border:1px solid #cc0000;padding:10px;background:#a40000;color:white;">
            <h1 style="border-bottom:1px solid #cc0000;font-size:16px;">%s <code>%d</code> in "%s" at line %d</h1>
            <p>%s</p>
            <p><pre>%s</pre></p>
        </div>
    </div>
ERR;
define('ERROR_TEMPLATE', $err);

function EX_Handler(Exception $e)
{
    printf(ERROR_TEMPLATE
        , get_class($e)
        , $e->getCode()
        , $e->getFile()
        , $e->getLine()
        , $e->getMessage()
        , $e->getTraceAsString());
    exit(1);
}

function ER_Handler( $errno ,  $errstr ,  $errfile ,  $errline ,  $errcontext  )
{
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
        , $context);
        exit(1);
    
}
set_error_handler('ER_Handler');
set_exception_handler('EX_Handler');
RSession::start();

//now we have a running session we might want to kill it
if(RURL::has('_destroy_session') || RURL::has('_bcms_logout') || RURL::has('_bambus_logout'))
{
    RSession::destroy();
}
//or create login differently
if(RSent::has('bambus_cms_login') && RSent::has('bambus_cms_username') && RSent::has('bambus_cms_password')){
    $triedToLogin = true;
}
PAuthentication::required();
 
$SUsersAndGroups = SUsersAndGroups::alloc()->init();


//some permission constants 
define('BAMBUS_USER_GROUPS', PAuthentication::getUserID() != '' ? implode('; ', $SUsersAndGroups->listGroupsOfUser(PAuthentication::getUserID())) : '');
define('BAMBUS_GRP_ADMINISTRATOR', $SUsersAndGroups->isMemberOf(PAuthentication::getUserID(), 'Administrator'));
define('BAMBUS_GRP_CREATE', $SUsersAndGroups->isMemberOf(PAuthentication::getUserID(), 'Create') || $SUsersAndGroups->isMemberOf(PAuthentication::getUserID(), 'Administrator'));
define('BAMBUS_GRP_RENAME', $SUsersAndGroups->isMemberOf(PAuthentication::getUserID(), 'Rename') || $SUsersAndGroups->isMemberOf(PAuthentication::getUserID(), 'Administrator'));
define('BAMBUS_GRP_DELETE', $SUsersAndGroups->isMemberOf(PAuthentication::getUserID(), 'Delete') || $SUsersAndGroups->isMemberOf(PAuthentication::getUserID(), 'Administrator'));
define('BAMBUS_GRP_EDIT', $SUsersAndGroups->isMemberOf(PAuthentication::getUserID(), 'Edit') || $SUsersAndGroups->isMemberOf(PAuthentication::getUserID(), 'Administrator'));
define('BAMBUS_GRP_CMS', $SUsersAndGroups->isMemberOf(PAuthentication::getUserID(), 'CMS') || $SUsersAndGroups->isMemberOf(PAuthentication::getUserID(), 'Administrator'));
define('BAMBUS_GRP_PHP', $SUsersAndGroups->isMemberOf(PAuthentication::getUserID(), 'PHP') || $SUsersAndGroups->isMemberOf(PAuthentication::getUserID(), 'Administrator'));
define('BAMBUS_PRIMARY_GROUP', $SUsersAndGroups->getPrimaryGroup(PAuthentication::getUserID()));

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