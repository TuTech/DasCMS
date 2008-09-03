<?php /*******************************************
* Bambus CMS 
* Created:     12.06.2006
* License:     GNU GPL Version 2 or later (http://www.gnu.org/copyleft/gpl.html)
* Copyright:   Lutz Selke/TuTech Innovation GmbH 
* Description: Image Administration Thumbnail creator
************************************************/
session_start();
define('BAMBUS_ACCESS_TYPE', 'management');
chdir('..');
require_once('./System/Classes/Bambus.php');
$Bambus = new Bambus();
//$Bambus->setMode('editor');
$allowedPaths = array('design', 'image');

//go to the cms root

//tell the bambus whats going on
list($get, $post, $session, $uploadfiles) = $Bambus->initialize($_GET,$_POST,$_SESSION,$_FILES);
@$bambus_user = utf8_decode((!empty($_SESSION['bambus_cms_username'])) ? $_SESSION['bambus_cms_username'] : $_SESSION['uname']);
@$bambus_password = utf8_decode((!empty($_SESSION['bambus_cms_password'])) ? $_SESSION['bambus_cms_password'] : $_SESSION['pwrd']);

$SUsersAndGroups = SUsersAndGroups::alloc()->init();

if($SUsersAndGroups->isValidUser($bambus_user, $bambus_password))
{
	$path = (!empty($get['path']) && $get['path'] == 'design') ? (SPath::DESIGN): (SPath::IMAGES);
	if(!empty($get['render']) && file_exists($path.basename($get['render']))){

        $image = $path.basename($get['render']);
        $thumb = SPath::TEMP.filemtime($path.basename($get['render'])).basename($get['render']).'.jpg';
       
       	if(file_exists($thumb))
       	{
       		readfile($thumb);
       		exit;
       	}
       
        //convert -size 128x128 Feed-icon.svg -resize 80x80 out.png
        $command = "/usr/bin/convert ".escapeshellarg($image)." -background white -flatten +matte -resize 96x96 ".escapeshellarg($thumb).";"
         ." /usr/bin/montage -geometry 96x96 ".escapeshellarg($thumb)." ".escapeshellarg($thumb)." ";
	
		@unlink($thumb);
		$output = shell_exec($command);



	    //looks like desired image size
	    $maxImageWidth  = 96;
	    $maxImageHeight = 96;
	    
	    //do some var init
	    $file = $path.basename($get['render']);
	    $suffixTemp = explode(".", $file);
	    $filetype = strtolower(array_pop($suffixTemp));
	    $thumbfile = $thumb;
	    //header('Content-Type: image/jpeg');
	    
	    //already there?
	    if(!file_exists($thumbfile)){
	        //just for the case we fail...
	        copy('./System/Images/no_preview.jpg', $thumbfile);
	        
	        //calculating size position etc
	        $size = GetImageSize($file);
	        $width = $size[0];
	        $height = $size[1];
	        $pos_x = 0 ;
	        $pos_y = 0 ;
	        if(($maxImageWidth / $width * $height) <  $maxImageHeight){
	            $newWidth  = $maxImageWidth;
	            $newHeight = round($maxImageWidth / $width * $height);
	        }else{
	            $newWidth  = round($maxImageHeight / $height * $width);
	            $newHeight = $maxImageHeight;
	        }
	        $pos_x = ($maxImageWidth == $newWidth) ? 0 : (($maxImageWidth/2) - ($newWidth/2));
	        $pos_y = ($maxImageHeight == $newHeight) ? 0 : ($maxImageHeight/2 - $newHeight/2);
	        
	        //open the image we want to shrink
	        if (($filetype=="jpg") || ($filetype=="jpeg")){
	            $image = ImageCreateFromJpeg($file);
	        }
	        if ($filetype=="gif"){
	             $image = imagecreatefromgif($file);
	        }
	        if ($filetype=="png"){
	            $image = ImageCreateFromPng($file);
	        }
	        $newImage = ImageCreateTrueColor($maxImageWidth,$maxImageHeight);
	        $farbe = imagecolorallocate($newImage,255,255,255);
	        imagefill($newImage,0,0,$farbe);
	        ImageCopyResized($newImage,$image,$pos_x,$pos_y,0,0,$newWidth,$newHeight,$width,$height);
	        //save it as jpeg
	        @ImageJPEG($newImage, $thumbfile, 60);
	        //ImageJPEG($newImage);
	        readfile($thumbfile);
	        @chmod($thumbfile, 0666);
	        //kill them and remove their dead bodies from the memory
	        ImageDestroy($image);
	        ImageDestroy($newImage);
	    }else{
	        readfile($thumbfile);
	    }
	}
}
?>