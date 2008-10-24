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
RSession::start();

//now we have a running session we might want to kill it
if(RURL::has('_destroy_session') || RURL::has('_bambus_logout'))
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

$tok = SProfiler::profile(__FILE__, __LINE__,'template engine');
//FIXME template from conf
$tpl = new TEngine('new_template', BTemplate::CONTENT, WTemplate::getGlobalEnvironment());
SProfiler::finish($tok);
$tok2 = SProfiler::profile(__FILE__, __LINE__,'template parsing');
echo $tpl->execute(array());
SProfiler::finish($tok2);
SProfiler::finish($tok);
?>