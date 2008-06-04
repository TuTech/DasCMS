<?php
/************************************************
* Bambus CMS 
* Created:     17. Okt 06
* License:     GNU GPL Version 2 or later (http://www.gnu.org/copyleft/gpl.html)
* Copyright:   Lutz Selke/TuTech Innovation GmbH 
* Description: 
************************************************/
define('BAMBUS_ACCESS_TYPE', 'management');
define('BAMBUS_CMS_DEFAULT_LANGUAGE', 'de');
chdir('..');
require_once('./System/Classes/Bambus.php');
setlocale (LC_ALL, 'de_DE');
$Bambus = new Bambus();
//$Bambus->setMode('editor');

//go to the cms root
session_start();

//tell the bambus whats going on
list($get, $post, $session, $uploadfiles) = $Bambus->initialize($_GET,$_POST,$_SESSION,$_FILES);
@$bambus_user = utf8_decode((!empty($_SESSION['bambus_cms_username'])) ? $_SESSION['bambus_cms_username'] : $_SESSION['uname']);
@$bambus_password = utf8_decode((!empty($_SESSION['bambus_cms_password'])) ? $_SESSION['bambus_cms_password'] : $_SESSION['pwrd']);

if($Bambus->UsersAndGroups->isValidUser($bambus_user, $bambus_password) && ($Bambus->UsersAndGroups->isMemberOf($bambus_user, 'Administrator') || $Bambus->UsersAndGroups->isMemberOf($bambus_user, 'Edit')))
{
	if(!empty($get['file']) && !empty($get['path']))
	{
		switch($get['path'])
		{
			case 'image':
				$path = $Bambus->pathTo('image');
				$files = $Bambus->FileSystem->getFiles('image', array('jpeg','jpg','png','gif','svg','mng','eps','ps','tif','tiff','psd','ai','pcx','wmf'));
				break;
			case 'download':
				$path = $Bambus->pathTo('download');
				$files = $Bambus->FileSystem->getFiles('download', array('php', 'cgi', 'php3', 'php4', 'php5', 'php6', 'asp', 'aspx', 'pl'), false);
				break;
			case 'design':
				$path = $Bambus->pathTo('design');
				$files = $Bambus->FileSystem->getFiles('design', array('css', 'gpl', 'jpeg','jpg','png','gif','svg','mng','eps','ps','tif','tiff','psd','ai','pcx','wmf'));
				break;
			case 'application':
				//FIXME: application permission check
			    if(file_exists($Bambus->pathTo('systemApplication').basename($_GET['editor']).'/Download.php'))
			    { 
			        include ($Bambus->pathTo('systemApplication').basename($_GET['editor']).'/Download.php');
			    }
			    else
			    {
			        die("No bambus for you, hungry Panda! - No Ajax controller");
			    }
				exit;
			default:
				die('No login? No bambus for you, hungry Panda!');
		}
		foreach($files as $file)
		{
			if($get['file'] == md5($file))
			{
                $FS = @FileSystem::alloc();
                @$FS->init();
                @$FS->writeLine($Bambus->pathTo('log').'files.log', sprintf("%s\t%s\t%s\t%s", date('r'), $bambus_user, 'download',$file));
				
				header('HTTP/1.1 200 OK');
				header('Status: 200 OK');
				header('Accept-Ranges: bytes');
				header('Content-Transfer-Encoding: Binary');
				header('Content-Type: application/force-download');
				header('Content-Disposition: inline; filename="'.trim($file).'"');
				readfile($path.$file);
				exit;
			}
		}
	}
}
else
{
	die('No login? No bambus for you, hungry Panda!');
}
?>
