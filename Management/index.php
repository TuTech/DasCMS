<?php /*******************************************
* Bambus CMS 
* Created:     12.06.2006
* License:     GNU GPL Version 2 or later (http://www.gnu.org/copyleft/gpl.html)
* Copyright:   Lutz Selke/TuTech Innovation GmbH 
* Description: Management (Login and Applcation loader)
************************************************/

chdir('..');
require_once('./System/Component/Loader.php');
WHeader::httpHeader('Content-Type: text/html; charset=utf-8');

RSession::start();
//you want to go? ok!
if(RURL::has('logout')){
    RSession::destroy(); 
    header('Location: ../?');
    exit;
}

PAuthentication::required();
WHeader::useScript('Management/localization.js.php');
WHeader::loadClientData();
WHeader::setBase(SLink::base());
WHeader::setTitle(BAMBUS_VERSION);
WHeader::meta('license', 'GNU General Public License/GPL 2 or newer');

WTemplate::globalSet('bcms_version', BAMBUS_VERSION);
WTemplate::globalSet('logout_text', SLocalization::get('logout'));
WTemplate::globalSet('WApplications', '');
WTemplate::globalSet('SNotificationCenter', SNotificationCenter::getSharedInstance());
WTemplate::globalSet('bambus_my_uri', SLink::link());
WTemplate::globalSet('Header', new WHeader());
WTemplate::globalSet('TaskBar','');
WTemplate::globalSet('SideBar','');
WTemplate::globalSet('OpenDialog','');
WTemplate::globalSet('ControllerData','');
WTemplate::globalSet('DocumentFormAction',  SLink::link());

if(PAuthorisation::has('org.bambuscms.login')) //login ok?
{
    WTemplate::globalSet('WApplications',  new WApplications());
    
    $App = SApplication::getSharedInstance();
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
                $controller->setTarget(RURL::get('edit', 'utf-8'));
            }
            //execute function call
            BAppController::callController(
                $controller, 
                RURL::get('_action'), 
                RSent::data('UTF-8')
            );
            if ($controller instanceof ISupportsOpenDialog) 
            {
            	WTemplate::globalSet(
                	'DocumentFormAction',  
                    SLink::link(array(
                    	'edit' => $controller->getOpenDialogTarget(), 
                    	'_action' => 'save'))
                );
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
		WTemplate::globalSet('SideBar',WSidePanel::getSharedInstance());
		WTemplate::renderOnce('header', WTemplate::SYSTEM);
		//do savings here - wsidebar might have done something
    	SApplication::appController()->commit();
    	
    	$erg = $App->getInterface();
    	if($erg !== true && (!file_exists($erg) || !include($erg)))
    	{
			//interface is coded in php an needs to be called here
			SNotificationCenter::report('alert', 'invalid_application');
    	}
		WTemplate::renderOnce('footer', WTemplate::SYSTEM);
    }
}
else
{
    WHeader::setTitle(
		'Bambus CMS: '.
	    SLocalization::get('login').' - '.
	    LConfiguration::get('sitename')
    );
    if(RSent::has('bambus_cms_login'))
    {
        SNotificationCenter::report('warning', 'wrong_username_or_password');
    }
    WTemplate::globalSet('logout_text', '');
    WTemplate::renderOnce('header', WTemplate::SYSTEM);
    $loginTpl = new WTemplate('login', WTemplate::SYSTEM);
    $loginTpl->setEnvironment(array(
        'translate:username' => SLocalization::get('username'),
        'translate:password' => SLocalization::get('password'),
        'translate:login' => SLocalization::get('login')
    ));
    $loginTpl->render();
    WTemplate::renderOnce('footer', WTemplate::SYSTEM);
}
?>