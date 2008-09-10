<?php /*******************************************
* Bambus CMS 
* Created:     12.06.2006
* License:     GNU GPL Version 2 or later (http://www.gnu.org/copyleft/gpl.html)
* Copyright:   Lutz Selke/TuTech Innovation GmbH 
* Description: Image Administration Thumbnail creator
************************************************/
chdir('..');
require_once('./System/Component/Loader.php');
$allowedPaths = array('design', 'image');

RSession::start();
PAuthentication::required();

$SUsersAndGroups = SUsersAndGroups::alloc()->init();

if(PAuthentication::isAuthenticated())
{
    $render = RURL::get('render');
	$path = (RURL::get('path') == 'design') ? (SPath::DESIGN): (SPath::IMAGES);
	if(file_exists($path.basename($render))){

        $image = $path.basename($render);
        $thumb = SPath::TEMP.filemtime($path.basename($render)).basename($render).'.jpg';
       
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
	    $file = $path.basename($render);
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