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

WTemplate::globalSet('bcms_version', BAMBUS_VERSION);
WTemplate::globalSet('WApplications', '');
WTemplate::globalSet('SNotificationCenter', SNotificationCenter::alloc()->init());
WTemplate::globalSet('bambus_my_uri', SLink::link());

/////////////////////////////////////
//load all related js and css files//
/////////////////////////////////////

$path = SPath::SYSTEM_STYLESHEETS;
$styles = DFileSystem::FilesOf($path, '/\.css$/i');
foreach ($styles as $css) 
{
	if(substr($css,-24) == 'specialPurpose.login.css')
		continue;
	WHeader::useStylesheet($path.$css);
}
$path = SPath::SYSTEM_SCRIPTS;
$jsPriorities = array(
    'global.js',
    'org.js',
    'org.bambuscms.js',
    'org.bambuscms.autorun.js'
);
foreach ($jsPriorities as $js) 
{
	WHeader::useScript($path.$js);
}
$scripts = DFileSystem::FilesOf($path, '/\.js$/i');
sort($scripts);//UC first
foreach ($scripts as $script) 
{
    if(!in_array($script, $jsPriorities))
    {
        WHeader::useScript($path.$script);
    }
}
WHeader::setBase(SLink::base());

WHeader::setTitle('BoxFish');
WHeader::meta('license', 'GNU General Public License/GPL 2 or newer');
WTemplate::globalSet('Header', new WHeader());

$SUsersAndGroups = SUsersAndGroups::alloc()->init();


if(PAuthorisation::has('org.bambus-cms.login')) //login ok?
{
	if(RSent::has('bambus_cms_username'))
	{
		$SUsersAndGroups->setUserAttribute(PAuthentication::getUserID(), 'last_management_login', time());
		$logins = $SUsersAndGroups->getUserAttribute(PAuthentication::getUserID(), 'management_login_count');
		$count = (empty($logins)) ? 1 : ++$logins;
		$SUsersAndGroups->setUserAttribute(PAuthentication::getUserID(), 'management_login_count', $count);
	}
	//this is not defined in a loop because code assist would not work otherwise

    //1st: validate application
    $applications = LApplication::getAvailableApplications();
    $Application = LApplication::alloc()->init();
    $tabs = $Application->selectApplicationFromPool($applications);
    
    WTemplate::globalSet('WApplications',  new WApplications());
    
 	//2nd: load application
    if(BAMBUS_APPLICATION == '')
	{
		WTemplate::globalSet('TaskBar','');
		define('BAMBUS_CURRENT_OBJECT', '');
		$headTpl = new WTemplate('header', WTemplate::SYSTEM);
    	$headTpl->render();
		echo LGui::beginApplication();
		echo LGui::endApplication();
        $footerTpl = new WTemplate('footer', WTemplate::SYSTEM);
        $footerTpl->render();
	}    
    else
    {
        $Application->initApp();
		//is there an application specific css or js file?
		$appFiles = array('style.css' => 'screen','print.css'=>'print', 'script.js' => 'script');
		foreach($appFiles as $file => $type)
		{
			if(!file_exists(BAMBUS_APPLICATION_DIRECTORY.$file))
				continue;
			switch($type)
			{
				case 'script':
					WHeader::useScript(BAMBUS_APPLICATION_DIRECTORY.$file);
					break;
				default: //css
					WHeader::useStylesheet(BAMBUS_APPLICATION_DIRECTORY.$file);
			}
		}
	    
	    WHeader::setTitle(BAMBUS_APPLICATION_TITLE.' - '.LConfiguration::get('sitename'));
	    
	    
	    //load additional translations from the application
	    
	    
	    
	    
	    
	    //export the config into an array
    	//load application class
    	$controller = $Application->controller();
    	$EditingObject = '';
    	if($controller != false)
    	{
    		ob_start();
    		require($controller);
    		$ob = ob_get_contents();
    		ob_end_clean();
    		$Application->autorun();
    	}
		define('BAMBUS_CURRENT_OBJECT', $EditingObject);
		WTemplate::globalSet('TaskBar',$Application->generateTaskBar());
    	$headTpl = new WTemplate('header', WTemplate::SYSTEM);
        $headTpl->render();
		echo $ob;
		echo LGui::beginApplication();
    	$erg = $Application->run();
    	if($erg !== true)
    	{
			//interface is coded in php an needs to be called here
			if(!file_exists($erg) || !include($erg))
			{
				SNotificationCenter::report('alert', 'invalid_application');
			}
    	}
		echo LGui::endApplication();
    	$footerTpl = new WTemplate('footer', WTemplate::SYSTEM);
        $footerTpl->render();
    }
}else{
    //Show Login
 
    WTemplate::globalSet('appNavigator', '');
    WTemplate::globalSet('TaskBar','');
    define('BAMBUS_APPLICATION_TITLE', SLocalization::get('login'));
    define('BAMBUS_APPLICATION_ICON', WIcon::pathFor('login'));
	define('BAMBUS_CURRENT_OBJECT', '');
	
    LApplication::alloc()->init()->selectApplicationFromPool(array());

	WHeader::useStylesheet('specialPurpose.login.css');
    if(RSent::has('bambus_cms_login'))
    {
        //sleep(10);
        SNotificationCenter::report('warning', 'wrong_username_or_password');
    }
    $headTpl = new WTemplate('header', WTemplate::SYSTEM);
    $headTpl->render();
    echo LGui::beginApplication();
    $loginTpl = new WTemplate('login', WTemplate::SYSTEM);
    $loginTpl->setEnvironment(array(
        'translate:username' => SLocalization::get('username'),
        'translate:password' => SLocalization::get('password'),
        'translate:login' => SLocalization::get('login')
    ));
    $loginTpl->render();
    echo LGui::endApplication();
    $footerTpl = new WTemplate('footer', WTemplate::SYSTEM);
    $footerTpl->render();
}
?>