<?php /*******************************************
* Bambus CMS 
* Created:     12.06.2006
* License:     GNU GPL Version 2 or later (http://www.gnu.org/copyleft/gpl.html)
* Copyright:   Lutz Selke/TuTech Innovation GmbH 
* Description: Management (Login and Applcation loader)
************************************************/

error_reporting(4095);
setlocale (LC_ALL, 'de_DE');
chdir('..');
require_once('./System/Classes/Bambus.php');
//We speak unicode xhtml 
//@todo
//header('Content-Type: text/html; charset=utf-8');
WHeader::httpHeader('Content-Type: text/html; charset=utf-8');
session_start();
//you want to go? ok!
if(!empty($_GET['logout'])){
    session_destroy();
    header('Location: ../?');
    exit;
}

define('BAMBUS_ACCESS_TYPE', 'management');

//got a new login?
if(!empty($_POST['bambus_cms_login']))
{
    $_SESSION['uname'] = (empty($_POST['username'])) 
    	? '' 
    	: $_POST['username'];
    $_SESSION['pwrd'] = (empty($_POST['password'])) 
    	? '' 
    	: $_POST['password'];
    $_SESSION['bambus_cms_username'] = (empty($_POST['bambus_cms_username'])) 
    	? '' 
    	: $_POST['bambus_cms_username'];
    $_SESSION['bambus_cms_password'] = (empty($_POST['bambus_cms_password'])) 
    	? '' 
    	: $_POST['bambus_cms_password'];
}

@$bambus_user = utf8_decode((!empty($_SESSION['bambus_cms_username'])) 
	? $_SESSION['bambus_cms_username'] 
	: $_SESSION['uname']);
@$bambus_password = utf8_decode((!empty($_SESSION['bambus_cms_password'])) 
	? $_SESSION['bambus_cms_password'] 
	: $_SESSION['pwrd']);

$_SESSION['language'] = (isset($_SESSION['language'])) ? $_SESSION['language'] :'de';
$_POST['language'] = (isset($_POST['language'])) ? $_POST['language'] : $_SESSION['language'];
$_SESSION['language'] = $_POST['language'];

//tell the bambus whats going on
$Bambus = new Bambus();

list($get, $post, $session, $uploadfiles) = $Bambus->initialize($_GET,$_POST,$_SESSION,$_FILES);


WTemplate::globalSet('logotext', BAMBUS_VERSION);
WTemplate::globalSet('WApplications', '');
WTemplate::globalSet('SNotificationCenter', SNotificationCenter::alloc()->init());
WTemplate::globalSet('bambus_my_uri', $Bambus->Linker->createQueryString(array(), true));


/////////////////////////////////////
//load all related js and css files//
/////////////////////////////////////

$path = $Bambus->pathTo('systemClientDataStylesheet');
$files = DFileSystem::FilesOf($path, '/\.css$/i');
foreach ($styles as $css) 
{
	if(substr($css,-24) == 'specialPurpose.login.css')
		continue;
	WHeader::useStylesheet($path.$css);
}
$path = $Bambus->pathTo('systemClientDataScript');
//$scripts = $Bam bus->File System->get Files('systemClientDataScript', 'js', true, true);
$scripts = DFileSystem::FilesOf($path, '/\.js$/i');
sort($scripts);//UC first
foreach ($scripts as $script) 
{
	WHeader::useScript($path.$script);
}
WHeader::setBase($Bambus->Linker->myBase());

WHeader::setTitle('BoxFish');
WHeader::meta('license', 'GNU General Public License/GPL 2 or newer');
WTemplate::globalSet('Header', new WHeader());
if($Bambus->UsersAndGroups->isValidUser($bambus_user, $bambus_password) && ($Bambus->UsersAndGroups->isMemberOf($bambus_user, 'CMS') || $Bambus->UsersAndGroups->isMemberOf($bambus_user, 'Administrator'))) //login ok?
{
	if(!empty($_POST['bambus_cms_login']))
	{
		$Bambus->UsersAndGroups->setUserAttribute($bambus_user, 'last_management_login', time());
		$logins = $Bambus->UsersAndGroups->getUserAttribute($bambus_user, 'management_login_count');
		$count = (empty($logins)) ? 1 : ++$logins;
		$Bambus->UsersAndGroups->setUserAttribute($bambus_user, 'management_login_count', $count);
	}
	//this is not defined in a loop because code assist would not work otherwise
	define('BAMBUS_USER', $bambus_user);
	define('BAMBUS_USER_GROUPS', implode('; ', $Bambus->UsersAndGroups->listGroupsOfUser(BAMBUS_USER)));
	define('BAMBUS_PRIMARY_GROUP', $Bambus->UsersAndGroups->getPrimaryGroup(BAMBUS_USER));
	define('BAMBUS_GRP_ADMINISTRATOR', $Bambus->UsersAndGroups->isMemberOf(BAMBUS_USER, 'Administrator'));
	define('BAMBUS_GRP_CREATE', $Bambus->UsersAndGroups->isMemberOf(BAMBUS_USER, 'Create') || $Bambus->UsersAndGroups->isMemberOf(BAMBUS_USER, 'Administrator'));
	define('BAMBUS_GRP_RENAME', $Bambus->UsersAndGroups->isMemberOf(BAMBUS_USER, 'Rename') || $Bambus->UsersAndGroups->isMemberOf(BAMBUS_USER, 'Administrator'));
	define('BAMBUS_GRP_DELETE', $Bambus->UsersAndGroups->isMemberOf(BAMBUS_USER, 'Delete') || $Bambus->UsersAndGroups->isMemberOf(BAMBUS_USER, 'Administrator'));
	define('BAMBUS_GRP_EDIT', $Bambus->UsersAndGroups->isMemberOf(BAMBUS_USER, 'Edit') || $Bambus->UsersAndGroups->isMemberOf(BAMBUS_USER, 'Administrator'));
	define('BAMBUS_GRP_CMS', $Bambus->UsersAndGroups->isMemberOf(BAMBUS_USER, 'CMS') || $Bambus->UsersAndGroups->isMemberOf(BAMBUS_USER, 'Administrator'));
	define('BAMBUS_GRP_PHP', $Bambus->UsersAndGroups->isMemberOf(BAMBUS_USER, 'PHP') || $Bambus->UsersAndGroups->isMemberOf(BAMBUS_USER, 'Administrator'));

    //1st: validate application
    $applications = $Bambus->getAvailableApplications();
    $tabs = $Bambus->selectApplicationFromPool($applications);
    WTemplate::globalSet('WApplications',  new WApplications(''));
    
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
	    
	    WHeader::setTitle(BAMBUS_APPLICATION_TITLE.' - '.$Bambus->Configuration->get('sitename'));
	    
	    
	    //load additional translations from the application
	    
	    
	    
	    
	    
	    //export the config into an array
		$Bambus->using('Application');    
    	//load application class

    	$controller = $Bambus->Application->controller();
    	$EditingObject = '';
    	if($controller != false)
    	{
    		ob_start();
    		require($controller);
    		$ob = ob_get_contents();
    		ob_end_clean();
    		$Bambus->Application->loadVars($get, $post, $session, $uploadfiles);
    	}
		define('BAMBUS_CURRENT_OBJECT', $EditingObject);
		WTemplate::globalSet('TaskBar',$Bambus->Application->generateTaskBar());
    	$headTpl = new WTemplate('header', WTemplate::SYSTEM);
        $headTpl->render();
		echo $ob;
		echo LGui::beginApplication();
    	$erg = $Bambus->Application->run();
    	if($erg !== true)
    	{
			//interface is coded in php an needs to be called here
			if(!file_exists($erg) || !include($erg))
			{
				SNotificationCenter::alloc()->init()->report('alert', 'invalid_application');
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
	define('BAMBUS_USER', '');
	
    $Bambus->selectApplicationFromPool(array());

	WHeader::useStylesheet('specialPurpose.login.css');
    if(!empty($_POST['bambus_cms_login']))
    {
        sleep(10);
        SNotificationCenter::alloc()->init()->report('warning', 'wrong_username_or_password');
    }
    $headTpl = new WTemplate('header', WTemplate::SYSTEM);
    $headTpl->render();
    echo LGui::beginApplication();
    $loginTpl = new WTemplate('login', WTemplate::SYSTEM);
    $loginTpl->setEnvornment(array(
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