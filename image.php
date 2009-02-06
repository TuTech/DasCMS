<?php
require_once('./System/Component/Loader.php');
header("Expires: ".date('r', strtotime('tomorrow')));
error_reporting(0);
RSession::start();
PAuthentication::required();
if(!empty($_SERVER['PATH_INFO']))
{
    $path = substr($_SERVER['PATH_INFO'],1);
    if(empty($path))
    {
        exit;
    }
    $parts = explode('/', $path);
    $alias = array_shift($parts);
    //resize key?
    $key = (count($parts)) ? array_shift($parts) : '';
    list($alias, $key) = explode('/', $path);
    $key = basename($key);
    if(preg_match(
        	'/^'.//'(_|c|p)'.//render type
        	//'([0-9A-Fa-f]*)-'.//render id 
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
        $id = WImage::getPreviewIdForContent(BContent::Open($alias));
        if(empty($id))
        {
            $id = '_';
        }
        $key = $id.'-'.$key;
        if(file_exists(SPath::TEMP.'scale.render.'.$key))
        {
            //image cached
            header('Last-modified: '.date('r',filemtime(SPath::TEMP.'scale.render.'.$key)));
            readfile(SPath::TEMP.'scale.render.'.$key);
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
                $img = Image_GD::load(SPath::SYSTEM_IMAGES.'inet-180.jpg');
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
            $img->save(SPath::TEMP.'scale.render.'.$key, 75, 'jpg');
            unlink(SPath::TEMP.'scale.permit.'.$key);
            header('Last-modified: '.date('r'));
            $img->generate('jpg');
        }
        else
        {
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
            $img = SPath::SYSTEM_IMAGES.'inet-180.jpg';
        }
        readfile($img);
    }
}
?>