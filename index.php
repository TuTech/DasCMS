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
header('Content-Type: text/html; charset=utf-8');
require_once('./System/Component/Loader.php');
define('BAMBUS_HTML_ACCESS', '1');
$_10Minutes = 600;
header("Expires: ".date('r', time()+$_10Minutes));
header("Cache-Control: max-age=".$_10Minutes.", public");
if(!LConfiguration::get('show_errors_on_website'))
{
    SErrorAndExceptionHandler::hideErrors();
}
if(LConfiguration::get('error_info_text_file') != '')
{
    SErrorAndExceptionHandler::showMessageBeforeDying();
}
//now we have a running session we might want to kill it
if(RURL::has('_destroy_session') || RURL::has('_bambus_logout'))
{
    RSession::destroy();
}
//or create login differently
if(RSent::has('bambus_cms_login') && RSent::has('bambus_cms_username') && RSent::has('bambus_cms_password')){
    $triedToLogin = true;
}
PAuthentication::implied();

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

$generatorAlias = LConfiguration::get('generator_content');
try
{
    $pageGenerator = Controller_Content::getSharedInstance()->openContent($generatorAlias);
}
catch (Exception $e)
{
    header("HTTP/1.1 500 Internal Server Error");
    header('Status: 500 Internal Server Error', true);
    SErrorAndExceptionHandler::reportException($e);
    $pageGenerator = new CError(500);
}
if ($pageGenerator instanceof IPageGenerator) 
{
	echo $pageGenerator->generatePage(WTemplate::getGlobalEnvironment());
}
else
{
    echo $pageGenerator->getContent();
}
?>