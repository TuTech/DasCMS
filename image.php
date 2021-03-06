<?php
/**
 * @todo cleanup code
 */
require_once 'System/main.php';
PAuthentication::implied();
$cache_1Day = 86400;
header("Expires: ".date('r', time()+$cache_1Day));
header("Cache-Control: max-age=".$cache_1Day.", public");
header("Content-Disposition: inline");
header('Pragma:');//disable "Pragma: no-cache" (default for sessions)

error_reporting(0);
if(!empty($_SERVER['PATH_INFO']))
{
    $path = substr($_SERVER['PATH_INFO'],1);
    if(empty($path))
    {
        exit;
    }
    $qual = intval(Core::Settings()->get('CFile_image_quality'));
	$overwriteQuality = 0;
    $parts = explode('/', $path);
    $alias = array_shift($parts);
    //resize key?
    $key = (count($parts)) ? array_shift($parts) : '';
    //list($alias, $key) = explode('/', $path);
    $key = basename($key);
    if(preg_match(
        	'/^'.
        	'([0-9A-Fa-f]+)-'.//width in hex
        	'([0-9A-Fa-f]+)-'.//height in hex
        	'([0-9])-'.//mode
        	'([a-zA-Z]+)-'.//force type
        	'([0-9A-Fa-f]+)-([0-9A-Fa-f]+)-([0-9A-Fa-f]+)$/'//r,g,b color
            ,$key, $match)
        )
    {
    	$ContentController = Controller_Content::getInstance();
    	if($ContentController->contentExists($alias))
    	{
	        try //public open
	        {
	            $content = $ContentController->accessContent($alias, new View_UIElement_Image(), true);
	        }
	        catch (Exception $e)
	        {
	        	$content = null;
	        }
	       	if($content == null && PAuthorisation::has('org.bambuscms.login'))
			{
	            try //private open
	            {
	                $content = $ContentController->openContent($alias);
	            }
	        	catch (Exception $e)
	        	{
					$content = null;
	        	}
			}
			elseif($content == null)
			{
				$content = new CError(403);
			}
    	}
    	else
    	{
    		$content = new CError(404);
    	}
		if($content == null){ //error
			$content = new CError(500);
		}

        //get the id of the preview image
        $id = View_UIElement_Image::getPreviewIdForContent($content);
        if(empty($id))
        {
            $id = '_';
        }
        $key = $id.'-'.$key;
        if(file_exists(Core::PATH_TEMP.'scale.render.'.$qual.'.'.$key))
        {
            //image cached
            header('Last-modified: '.date('r',filemtime(Core::PATH_TEMP.'scale.render.'.$qual.'.'.$key)));
            header('Content-type: image/jpeg');
            readfile(Core::PATH_TEMP.'scale.render.'.$qual.'.'.$key);
            exit;
        }
        //permitted to scale?
        $userHasPermission = PAuthorisation::has('org.bambuscms.bcontent.previewimage.create');
        if(!$userHasPermission && !file_exists(Core::PATH_TEMP.'scale.permit.'.$key))
        {
            //slow file system? retry in 1 sec
            sleep(1);
        }
        if(file_exists(Core::PATH_TEMP.'scale.permit.'.$key) || $userHasPermission)
        {
            list($nil, $width, $height, $mode, $force, $r, $g, $b) = $match;
            //unhex
            foreach(array('width', 'height', 'r', 'g', 'b') as $var)
            {
                ${$var} = hexdec(${$var});
            }
            if($id == '_')//load cms default image
            {
                //default img
                $img = Image_GD::load(View_UIElement_Image::placeholderFile());
				$force = View_UIElement_Image::FORCE_BY_FILL;
				$r = $g = $b = 255;
				$mode = View_UIElement_Image::MODE_FORCE;
				$overwriteQuality = 100;
            }
            else //render preview
            {
                $alias = View_UIElement_Image::resolvePreviewId($id);
                if(!empty($alias))
                {
                    $c = Controller_Content::getInstance()->openContent($alias);
                    $img = Image_GD::load($c->getRawDataPath(), $c->getType());
                }
            }
            //no preview... make image with bg-color
            if(!$img)
            {
                $img = Image_GD::create($width, $height);
                $img->fill($img->makeColor($r, $g, $b));
            }
            if($mode)//resize to fixed size img
            {
                //forced
                if($force == View_UIElement_Image::FORCE_BY_CROP)
                {
                    $img = $img->cropscale($width, $height);
                }
                elseif($force == View_UIElement_Image::FORCE_BY_FILL)
                {
                    $img = $img->fillscale($width, $height, $img->makeColor($r,$g,$b));
                }
                elseif($force == View_UIElement_Image::FORCE_BY_STRETCH)
                {
                    $img = $img->stretchscale($width, $height);
                }
            }
            else //resite image to fit in boundaries
            {
                $img = $img->scaletofit($width, $height);
            }
            //save and send image
            $imgFile = Core::PATH_TEMP.'scale.render.'.$qual.'.'.$key;
            $permFile = Core::PATH_TEMP.'scale.permit.'.$key;
            $time = time();
            if(file_exists($imgFile))
            {
                $time = filemtime($imgFile);
            }
            if($img->isModified())
            {
                header('Content-type: image/jpeg');
                header('Last-modified: '.date('r',$time));
                $img->save($imgFile, $qual, 'jpg');
                $img->generate('jpg', max($qual, $overwriteQuality));
            }
            else
            {
                header('Last-modified: '.date('r',filemtime($img->getSourceFile())));
                copy($img->getSourceFile(), $imgFile);
                touch($imgFile, filemtime($img->getSourceFile()));
                readfile($img->getSourceFile());
            }
            if(file_exists($permFile))
            {
                @unlink($permFile);
            }
        }
    }
    else
    {
        $img = false;
        $id = View_UIElement_Image::getPreviewIdForContent(Controller_Content::getInstance()->tryOpenContent($alias));
        if($id != '_')//load cms default image
        {
            $alias = View_UIElement_Image::resolvePreviewId($id);
            if(!empty($alias))
            {
                $c = Controller_Content::getInstance()->openContent($alias);
                header('Content-type: '.$c->getType());
                $img = $c->getRawDataPath();
            }
        }
        if(!$img)
        {
            header('Content-type: image/jpeg;');
            $img = View_UIElement_Image::placeholderFile();
        }
        header('Last-modified: '.date('r',filemtime($img)));
        readfile($img);
    }
}
?>