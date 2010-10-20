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
View_UIElement_Header::httpHeader('Content-Type: text/html; charset='.CHARSET);

RSession::start();
//you want to go? ok!
if(RURL::has('logout')){
    RSession::destroy(); 
    header('Location: ../?');
    exit;
}

PAuthentication::required();
$media = Core::dataFromJSONFile('Content/versioninfo.json');
if(isset($media['js']))View_UIElement_Header::useScript($media['js']);
if(isset($media['css']))View_UIElement_Header::useStylesheet($media['css']);
View_UIElement_Header::useScript('Management/localization.js.php');
View_UIElement_Header::setBase(SLink::base());
View_UIElement_Header::setTitle(BAMBUS_VERSION);
View_UIElement_Header::meta('license', 'GNU General Public License/GPL 2 or newer');

View_UIElement_Template::globalSet('bcms_version', BAMBUS_VERSION);
View_UIElement_Template::globalSet('logout_text', SLocalization::get('logout'));
View_UIElement_Template::globalSet('View_UIElement_Applications', '');
View_UIElement_Template::globalSet('SNotificationCenter', SNotificationCenter::getInstance());
View_UIElement_Template::globalSet('bambus_my_uri', SLink::link());
View_UIElement_Template::globalSet('Header', new View_UIElement_Header());
View_UIElement_Template::globalSet('TaskBar','');
View_UIElement_Template::globalSet('SideBar','');
View_UIElement_Template::globalSet('OpenDialog','');
View_UIElement_Template::globalSet('ControllerData','');
View_UIElement_Template::globalSet('ContentAlias','');
View_UIElement_Template::globalSet('DocumentFormAction',  SLink::link());

if(PAuthorisation::has('org.bambuscms.login')) //login ok?
{
    View_UIElement_Template::globalSet('View_UIElement_Applications',  new View_UIElement_Applications());
    
    $App = SApplication::getInstance();
    $App->initApplication();
    
    if(!$App->hasApplication())
	{
		View_UIElement_Template::renderOnce('header', View_UIElement_Template::SYSTEM);
		View_UIElement_Template::renderOnce('footer', View_UIElement_Template::SYSTEM);
	}    
    else
    {
	    View_UIElement_Template::globalSet('AppGUID',$App->getGUID());
    	
	    $ctrl = $App->getController();
	    if($ctrl)
	    {
	        //controller file
    		ob_start();
    		require($ctrl);
    		$ob = ob_get_contents();
    		ob_end_clean();
    		View_UIElement_Template::globalSet('ControllerData',$ob);
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
            _Controller_Application::callController(
                $controller, 
                RURL::get('_action'), 
                RSent::data(CHARSET)
            );
            if ($controller instanceof ISupportsOpenDialog) 
            {
            	View_UIElement_Template::globalSet(
                	'DocumentFormAction',  
                    SLink::link(array(
                    	'edit' => $controller->getOpenDialogTarget(), 
                    	'_action' => 'save'))
                );
                View_UIElement_Template::globalSet('ContentAlias',$controller->getOpenDialogTarget());
            }
            else
            {
                View_UIElement_Template::globalSet(
                	'DocumentFormAction' 
                    ,SLink::link(array('_action' => 'save'))
                );
            }
	    }
	    
		View_UIElement_Template::globalSet('TaskBar',$App->getTaskBar());//
		View_UIElement_Template::globalSet('OpenDialog',$App->getOpenDialog());
		View_UIElement_Template::globalSet('SideBar',View_UIElement_SidePanel::getInstance());
		View_UIElement_Template::renderOnce('header', View_UIElement_Template::SYSTEM);
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
		View_UIElement_Template::globalSet('gentime', sprintf(
				"\t\t<!-- %s/%s/%1.5f -->\n",
				memory_get_usage(true),
				memory_get_peak_usage(true),
				$end_time-$start_time
			));
		View_UIElement_Template::renderOnce('footer', View_UIElement_Template::SYSTEM);
    }
}
else
{
    View_UIElement_Header::setTitle(
		'Bambus CMS: '.
	    SLocalization::get('login').' - '.
	    Core::Settings()->get('sitename')
    );
    if(RSent::has('bambus_cms_login'))
    {
        SNotificationCenter::report('warning', 'wrong_username_or_password');
    }
    View_UIElement_Template::globalSet('logout_text', '');
    View_UIElement_Template::renderOnce('header', View_UIElement_Template::SYSTEM);
    $loginTpl = new View_UIElement_Template('cmslogin', View_UIElement_Template::SYSTEM);
    $loginTpl->setEnvironment(array(
        'translate:username' => SLocalization::get('username'),
        'translate:password' => SLocalization::get('password'),
        'translate:login' => SLocalization::get('login')
    ));
    $loginTpl->render();
	View_UIElement_Template::globalSet('gentime','');
    View_UIElement_Template::renderOnce('footer', View_UIElement_Template::SYSTEM);
}
?>