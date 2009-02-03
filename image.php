<?php
//header('Content-type: image/jpg');
require_once('./System/Component/Loader.php');
header('Content-type: image/jpeg;');
header("Expires: ".date('r', strtotime('tomorrow')));
error_reporting(0);
RSession::start();
PAuthentication::required();
if(!empty($_SERVER['PATH_INFO']))
{
    $path = substr($_SERVER['PATH_INFO'],1);
    list($alias, $key) = explode('/', $path);
    $key = base64_decode($key);
    if(preg_match(
        	'/^(_|c|p)'.//render type
        	'([0-9A-Fa-f]*)-'.//render id 
        	'([0-9A-Fa-f]+)-'.//width in hex
        	'([0-9A-Fa-f]+)-'.//height in hex
        	'([0-9])-'.//mode
        	'([a-zA-Z]+)-'.//force type
        	'([0-9A-Fa-f]+)-([0-9A-Fa-f]+)-([0-9A-Fa-f]+)$/'//r,g,b color
            ,$key, $match)
        )
    {
        if(file_exists(SPath::TEMP.'scale.render.'.$key))
        {
            header('Last-modified: '.date('r',filemtime(SPath::TEMP.'scale.render.'.$key)));
            readfile(SPath::TEMP.'scale.render.'.$key);
            exit;
        }
        if(!file_exists(SPath::TEMP.'scale.permit.'.$key))
        {
            sleep(1);
        }
        if(file_exists(SPath::TEMP.'scale.permit.'.$key))
        {     
            //scale!
            //1st copy dummy
            //         [1] => _ [2] => [3] => a [4] => a [5] => 0 [6] => f [7] => ff [8] => ff [9] => ff 
            list($nil, $type,   $id,   $width,  $height, $mode,   $force,  $r,       $g,       $b) = $match;
            //unhex
            foreach(array('width', 'height', 'r', 'g', 'b') as $var)
            {
                ${$var} = hexdec(${$var});
            }
            //load cms default image 
            if($type == '_')
            {
                //default img
                $img = Image_GD::load(SPath::SYSTEM_IMAGES.'inet-180.jpg');
            }
            elseif($type == 'c')//content itself
            {
                $c = BContent::OpenIfPossible($alias);
                if($c instanceof IFileContent)
                {
                    $img = Image_GD::load($c->getRawDataPath(), $c->getType());
                }
            }
            elseif($type == 'p')//preview for content
            {
                $alias = WImage::resolvePreviewId($id);
                if(!empty($alias))
                {
                    $c = BContent::OpenIfPossible($alias);
                    $img = Image_GD::load($c->getRawDataPath(), $c->getType());
                }
            }
            if(!$img)
            {
                $img = Image_GD::create($width, $height);
                    $img->fill($img->makeColor($r, $g, $b)); 
            }
            if($mode)
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
            else
            {
                $img = $img->scaletofit($width, $height); 
            }
            $img->save(SPath::TEMP.'scale.render.'.$key, 75, 'jpg');
            unlink(SPath::TEMP.'scale.permit.'.$key);
            header('Last-modified: '.date('r'));
            $img->generate('jpg');
        }
    }
    else
    {
        readfile(SPath::SYSTEM_IMAGES.'inet-180.jpg');
    }
}
?>