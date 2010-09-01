<?php /*******************************************
* Bambus CMS 
* Created:     12.06.2006
* License:     GNU GPL Version 2 or later (http://www.gnu.org/copyleft/gpl.html)
* Copyright:   Lutz Selke/TuTech Innovation GmbH 
* Description: Management (Login and Applcation loader)
************************************************/

$start_time = microtime(true);
chdir(dirname(__FILE__));
require_once '../System/main.php';
WHeader::httpHeader('Content-Type: text/html; charset='.CHARSET);

RSession::start();
//you want to go? ok!
if(RURL::has('logout')){
    RSession::destroy(); 
    header('Location: ../?');
    exit;
}

PAuthentication::required();
$media = Core::dataFromJSONFile('Content/versioninfo.json');
if(isset($media['js']))WHeader::useScript($media['js']);
if(isset($media['css']))WHeader::useStylesheet($media['css']);
WHeader::useScript('Management/localization.js.php');
WHeader::setBase(SLink::base());
WHeader::setTitle(BAMBUS_VERSION);
WHeader::meta('license', 'GNU General Public License/GPL 2 or newer');

WTemplate::globalSet('bcms_version', BAMBUS_VERSION);
WTemplate::globalSet('logout_text', SLocalization::get('logout'));
WTemplate::globalSet('WApplications', '');
WTemplate::globalSet('SNotificationCenter', SNotificationCenter::getInstance());
WTemplate::globalSet('bambus_my_uri', SLink::link());
WTemplate::globalSet('Header', new WHeader());
WTemplate::globalSet('TaskBar','');
WTemplate::globalSet('SideBar','');
WTemplate::globalSet('OpenDialog','');
WTemplate::globalSet('ControllerData','');
WTemplate::globalSet('ContentAlias','');
WTemplate::globalSet('DocumentFormAction',  SLink::link());

if(PAuthorisation::has('org.bambuscms.login')) //login ok?
{
    WTemplate::globalSet('WApplications',  new WApplications());
    
    $App = SApplication::getInstance();
    $App->initApplication();
    
    if(!$App->hasApplication())
	{
		WTemplate::renderOnce('header', WTemplate::SYSTEM);
		WTemplate::renderOnce('footer', WTemplate::SYSTEM);
	}    
    else
    {
	    WTemplate::globalSet('AppGUID',$App->getGUID());
    	
	    $ctrl = $App->getController();
	    if($ctrl)
	    {
	        //controller file
    		ob_start();
    		require($ctrl);
    		$ob = ob_get_contents();
    		ob_end_clean();
    		WTemplate::globalSet('ControllerData',$ob);
	    }
	    else
	    {
	        //generic controller interface
	        $controller = SApplication::appController();
            //target from url
            if(RURL::has('edit'))
            {
                $controller->setTarget(RURL::get('edit', CHARSET));
            }
            //execute function call
            BAppController::callController(
                $controller, 
                RURL::get('_action'), 
                RSent::data(CHARSET)
            );
            if ($controller instanceof ISupportsOpenDialog) 
            {
            	WTemplate::globalSet(
                	'DocumentFormAction',  
                    SLink::link(array(
                    	'edit' => $controller->getOpenDialogTarget(), 
                    	'_action' => 'save'))
                );
                WTemplate::globalSet('ContentAlias',$controller->getOpenDialogTarget());
            }
            else
            {
                WTemplate::globalSet(
                	'DocumentFormAction' 
                    ,SLink::link(array('_action' => 'save'))
                );
            }
	    }
	    
		WTemplate::globalSet('TaskBar',$App->getTaskBar());//
		WTemplate::globalSet('OpenDialog',$App->getOpenDialog());
		WTemplate::globalSet('SideBar',WSidePanel::getInstance());
		WTemplate::renderOnce('header', WTemplate::SYSTEM);
		//do savings here - wsidebar might have done something
    	if(count(RSent::data()) > 0)
    	{
    	    SApplication::appController()->commit();
    	}
    	
    	$erg = $App->getInterface();
    	if($erg !== true && (!file_exists($erg) || !include($erg)))
    	{
			//interface is coded in php an needs to be called here
			SNotificationCenter::report('alert', 'invalid_application');
    	}
		$end_time = microtime(true);
		WTemplate::globalSet('gentime','<!-- gen time '.($end_time-$start_time).'s -->');
		WTemplate::renderOnce('footer', WTemplate::SYSTEM);
    }
}
else
{
    WHeader::setTitle(
		'Bambus CMS: '.
	    SLocalization::get('login').' - '.
	    Core::settings()->get('sitename')
    );
    if(RSent::has('bambus_cms_login'))
    {
        SNotificationCenter::report('warning', 'wrong_username_or_password');
    }
    WTemplate::globalSet('logout_text', '');
    WTemplate::renderOnce('header', WTemplate::SYSTEM);
    $loginTpl = new WTemplate('cmslogin', WTemplate::SYSTEM);
    $loginTpl->setEnvironment(array(
        'translate:username' => SLocalization::get('username'),
        'translate:password' => SLocalization::get('password'),
        'translate:login' => SLocalization::get('login')
    ));
    $loginTpl->render();
	WTemplate::globalSet('gentime','');
    WTemplate::renderOnce('footer', WTemplate::SYSTEM);
}
?>