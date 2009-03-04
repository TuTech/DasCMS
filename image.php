<?php
require_once('./System/Component/Loader.php');
RSession::start();
PAuthentication::required();
$cache_1Day = 86400;
header("Expires: ".date('r', time()+$cache_1Day));
header("Cache-Control: max-age=".$cache_1Day.", public");
error_reporting(0);
if(!empty($_SERVER['PATH_INFO']))
{
    $path = substr($_SERVER['PATH_INFO'],1);
    if(empty($path))
    {
        exit;
    }
    $qual = intval(LConfiguration::get('preview_image_quality'));
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
        header('Content-type: image/jpeg;');
        //get the id of the preview image 
        if(PAuthorisation::has('org.bambuscms.login'))
        {
            //valid user - allowed to view unpublished images
            $content = BContent::Open($alias);
        }
        else
        {
            //only allowed to view public images
            $content = BContent::Access($alias, new WImage());
        } 
        $id = WImage::getPreviewIdForContent($content);
        if(empty($id))
        {
            $id = '_';
        }
        $key = $id.'-'.$key;
        if(file_exists(SPath::TEMP.'scale.render.'.$qual.'.'.$key))
        {
            //image cached
            header('Last-modified: '.date('r',filemtime(SPath::TEMP.'scale.render.'.$qual.'.'.$key)));
            readfile(SPath::TEMP.'scale.render.'.$qual.'.'.$key);
            exit;
        }
        //permitted to scale?
        $userHasPermission = PAuthorisation::has('org.bambuscms.bcontent.previewimage.create');
        if(!$userHasPermission && !file_exists(SPath::TEMP.'scale.permit.'.$key))
        {
            //slow file system? retry in 1 sec
            sleep(1);
        }
        if(file_exists(SPath::TEMP.'scale.permit.'.$key) || $userHasPermission)
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
                $img = Image_GD::load(SPath::SYSTEM_IMAGES.'no_preview.jpg');
            }
            else //render preview
            {
                $alias = WImage::resolvePreviewId($id);
                if(!empty($alias))
                {
                    $c = BContent::OpenIfPossible($alias);
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
                if($force == WImage::FORCE_BY_CROP)
                {
                    $img = $img->cropscale($width, $height);
                }
                elseif($force == WImage::FORCE_BY_FILL)
                {
                    $img = $img->fillscale($width, $height, $img->makeColor($r,$g,$b));
                }
                elseif($force == WImage::FORCE_BY_STRETCH)
                {
                    $img = $img->stretchscale($width, $height);
                }
            }
            else //resite image to fit in boundaries
            {
                $img = $img->scaletofit($width, $height); 
            }
            //save and send image
            $imgFile = SPath::TEMP.'scale.render.'.$qual.'.'.$key;
            $permFile = SPath::TEMP.'scale.permit.'.$key;
            $time = time();
            $img->save($imgFile, $qual, 'jpg');
            if(file_exists($permFile))
            {
                @unlink($permFile);
            }
            if(file_exists($imgFile))
            {
                $time = filemtime($imgFile);
            }
            header('Last-modified: '.date('r',$time));
            $img->generate('jpg', $qual);
        }
    }
    else
    {
        $img = false;
        $id = WImage::getPreviewIdForContent(BContent::Open($alias));
        if($id != '_')//load cms default image 
        {
            $alias = WImage::resolvePreviewId($id);
            if(!empty($alias))
            {
                $c = BContent::OpenIfPossible($alias);
                header('Content-type: '.$c->getType());
                $img = $c->getRawDataPath();
            }
        }
        if(!$img)
        {
            header('Content-type: image/jpeg;');
            $img = SPath::SYSTEM_IMAGES.'no_preview.jpg';
        }
        header('Last-modified: '.date('r',filemtime($img)));
        readfile($img);
    }
}
?>