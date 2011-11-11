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
require_once 'System/main.php';
define('BAMBUS_HTML_ACCESS', '1');
$_10Minutes = 600;
header("Expires: ".date('r', time()+$_10Minutes));
header("Cache-Control: max-age=".$_10Minutes.", public");
if(!Core::Settings()->get('show_errors_on_website'))
{
    SErrorAndExceptionHandler::hideErrors();
}
if(Core::Settings()->get('error_info_text_file') != '')
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
View_UIElement_Template::globalSet('meta_keywords',Core::Settings()->get('meta_keywords'));
View_UIElement_Template::globalSet('bambus_version', BAMBUS_VERSION);
View_UIElement_Template::globalSet('rssfeeds', '');
View_UIElement_Template::globalSet('bambus_my_uri', SLink::buildURL());
View_UIElement_Template::globalSet('logout_href', SLink::link(array('_destroy_session' => '1')));

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
View_UIElement_Template::globalSet('stylesheets', $stylesheetLinks);

$generatorAlias = Core::Settings()->get('generator_content');
try
{
    $pageGenerator = Controller_Content::getInstance()->openContent($generatorAlias);
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
	echo $pageGenerator->generatePage(View_UIElement_Template::getGlobalEnvironment());
}
else
{
    echo $pageGenerator->getContent();
}
?>