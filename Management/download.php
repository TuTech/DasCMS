<?php
/************************************************
* Bambus CMS 
* Created:     17. Okt 06
* License:     GNU GPL Version 2 or later (http://www.gnu.org/copyleft/gpl.html)
* Copyright:   Lutz Selke/TuTech Innovation GmbH 
* Description: 
************************************************/
chdir('..');
require_once('./System/Component/Loader.php');

RSession::start();
PAuthentication::required();
 
//tell the bambus whats going on

$SUsersAndGroups = SUsersAndGroups::alloc()->init();

if(PAuthorisation::has('org.bambuscms.login'))
{
    $fileName = RURL::get('file');
    $pathName = RURL::get('path');
	if(!empty($file) && !empty($pathName))
	{
		switch($pathName)
		{
			case 'image':
				$path = SPath::IMAGES;
				$files = DFileSystem::FilesOf($path, '/\.(jpe?g|png|gif|svg|mng|e?ps|tiff?|psd|ai|pcx|wmf)$/i');
				break;
			case 'download':
				$path = SPath::DOWNLOADS;
				$files = DFileSystem::FilesOf($path, '/(?!\.php[0-9]?|\.aspx?|\.pl|\.phtml|\.cgi)$/i');
				break;
			case 'design':
				$path = SPath::DESIGN;
				$files = DFileSystem::FilesOf($path, '/\.(css|gpl|jpe?g|png|gif|svg|mng|e?ps|tiff?|psd|ai|pcx|wmf)$/i');
				break;
			case 'application':
				//FIXME: application permission check
			    if(file_exists(SPath::SYSTEM_APPLICATIONS.basename(RURL::get('editor')).'/Download.php'))
			    { 
			        include (SPath::SYSTEM_APPLICATIONS.basename(RURL::get('editor')).'/Download.php');
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
			if($fileName == md5($file))
			{
				DFileSystem::Append(SPath::LOGS.'files.log', sprintf("%s\t%s\t%s\t%s\n", date('r'), PAuthentication::getUserID(), 'download',$file));
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
