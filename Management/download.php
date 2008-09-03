<?php
/************************************************
* Bambus CMS 
* Created:     17. Okt 06
* License:     GNU GPL Version 2 or later (http://www.gnu.org/copyleft/gpl.html)
* Copyright:   Lutz Selke/TuTech Innovation GmbH 
* Description: 
************************************************/
define('BAMBUS_ACCESS_TYPE', 'management');
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

$SUsersAndGroups = SUsersAndGroups::alloc()->init();

if($SUsersAndGroups->isValidUser($bambus_user, $bambus_password) && ($SUsersAndGroups->isMemberOf($bambus_user, 'Administrator') || $SUsersAndGroups->isMemberOf($bambus_user, 'Edit')))
{
	if(!empty($get['file']) && !empty($get['path']))
	{
		switch($get['path'])
		{
			case 'image':
				$path = SPath::IMAGES;
				$files = DFileSystem::FilesOf($path, '/\.(jpe?g|png|gif|svg|mng|e?ps|tiff?|psd|ai|pcx|wmf)$/i');
				//$files = $Bam bus->File System->get Files('image', array('jpeg','jpg','png','gif','svg','mng','eps','ps','tif','tiff','psd','ai','pcx','wmf'));
				break;
			case 'download':
				$path = SPath::DOWNLOADS;
				//$files = $Bam bus->File System->get Files('download', array('php', 'cgi', 'php3', 'php4', 'php5', 'php6', 'asp', 'aspx', 'pl'), false);
				$files = DFileSystem::FilesOf($path, '/\.(?!(php[0-9]?|aspx?|pl|phtml|cgi))$/i');
				break;
			case 'design':
				$path = SPath::DESIGN;
				//$files = $Bam bus->File System->get Files('design', array('css', 'gpl', 'jpeg','jpg','png','gif','svg','mng','eps','ps','tif','tiff','psd','ai','pcx','wmf'));
				$files = DFileSystem::FilesOf($path, '/\.(css|gpl|jpe?g|png|gif|svg|mng|e?ps|tiff?|psd|ai|pcx|wmf)$/i');
				break;
			case 'application':
				//FIXME: application permission check
			    if(file_exists(SPath::SYSTEM_APPLICATIONS.basename($_GET['editor']).'/Download.php'))
			    { 
			        include (SPath::SYSTEM_APPLICATIONS.basename($_GET['editor']).'/Download.php');
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
				DFileSystem::Append(SPath::LOGS.'files.log', sprintf("%s\t%s\t%s\t%s\n", date('r'), $bambus_user, 'download',$file));
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
