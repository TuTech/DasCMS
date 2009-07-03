<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2009-01-30
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage _Image
 */
class Image_GD extends _Image
{
    protected $imgRes;
    protected $file = null;
    
    protected function __construct(){}
    
    public function __destruct()
    {
        imagedestroy($this->imgRes);
    }
    
    /**
     * create new image
     * @param int $width
     * @param int $height
     * @return Image_GD
     */
    public static function create($width, $height)
    {
        $img = new Image_GD();
        $img->imgRes = imagecreatetruecolor($width, $height);
        return $img;
    }
    
    /**
     * @param string $file
     * @return Image_GD
     */
    public static function load($file, $type = null)
    {
        $img = new Image_GD();
        $img->file = $file;
        $suf = ($type == null) ? (DFileSystem::suffix($file)) : strtolower($type);
        switch ($suf) 
        {
        	case 'jpg':
        	case 'jpeg':
        	    $img->imgRes = imagecreatefromjpeg($file);
        		break;
        	case 'png':
        	    $img->imgRes = imagecreatefrompng($file);
        		break;
        	case 'gif':
        	    $img->imgRes = imagecreatefromgif($file);
        	    break;
    		case 'bmp':
        	    $img->imgRes = imagecreatefromwbmp($file);
        		break;
        	case 'xbm':
        	    $img->imgRes = imagecreatefromxbm($file);
        		break;
        	default:
        		throw new Exception('unsupported image');
        	break;
        }
        if(!imageistruecolor($img->imgRes))
        {
            //convert to true color
            $w = imagesx($img->imgRes);
            $h = imagesy($img->imgRes);
            $tmp = imagecreatetruecolor($w, $h);
            imagecopy($tmp,$img->imgRes,0,0,0,0,$w,$h);
            imagedestroy($img->imgRes);
            $img->imgRes = $tmp;
        }
        return $img;
    }
    
    /**
     * @param string $file
     * @param int $quality 0..100
     * @param string $type jpg,png,bmp,xbm
     * @return void
     */
    public function save($file, $quality = 75, $type = null)
    {
        $suf = ($type == null) ? (DFileSystem::suffix($file)) : strtolower($type);
        switch ($suf) 
        {
        	case 'jpg':
        	case 'jpeg':
        	    imagejpeg($this->imgRes, $file, $quality);
        		break;
        	case 'png':
        	    imagepng($this->imgRes, $file, $quality);
        		break;
        	case 'bmp':
        	    imagewbmp($this->imgRes, $file);
        		break;
        	case 'xbm':
        	    imagexbm($this->imgRes, $file);
        		break;
        	default:
        		throw new Exception('unsupported suffix');
        	break;
        }
    }
    
    public function isModified()
    {
        return $this->file == null;
    }
    
    public function getSourceFile()
    {
        return $this->file;
    }
    
    /**
     * write image data to stdout
     * @param string $type jpg,png,bmp,xbm
     * @param int $quality 0..100
     * @return void
     */
    public function generate($type = 'jpg', $quality = 75)
    {
        $this->save(null, $quality, $type);
    }
    
    /**
     * @param color $color
     * @param int $x
     * @param int $y
     * @return void
     */
    public function fill($color,$x = 0,$y = 0)
    {
        imagefill($this->imgRes,$x,$y,$color);
    }
    
    /**
     * @param int $r
     * @param int $g
     * @param int $b
     * @param int $alpha
     * @return color
     */
    public function makeColor($r, $g, $b, $alpha = null)
    {
        if($alpha)
        {
            return imagecolorallocatealpha($this->imgRes, $r, $g, $b, $alpha);
        }
        else
        {
            return imagecolorallocate($this->imgRes, $r, $g, $b);
        }
    }
    
    private function setAntialias(&$imgRes, $on = true)
    {
        if(function_exists('imageantialias'))
        {
            imageantialias($imgRes, $on);
        }
    }
    
    /**
     * fixed size scale method
     * @param $width
     * @param $heigth
     * @return Image_GD
     */
    public function cropscale($width, $heigth)
    {
        //get x and y scale factor
        $oldWidth = imagesx($this->imgRes);
        $oldHeight = imagesy($this->imgRes);
        if($width != $oldWidth || $heigth != $oldHeight)
        {
            //scale
            $xscale = $width/$oldWidth;
            $yscale = $heigth/$oldHeight;
            $scale = ($xscale >= $yscale) ? $xscale : $yscale;
            
            //locate start offset in original file
            $startx = ceil(($oldWidth  - $width/$scale)/2);
            $starty = ceil(($oldHeight  - $heigth/$scale)/2);
            
            //create and copy crop
            $target = Image_GD::create($width, $heigth);
            $this->setAntialias($target->imgRes, true);
            imagecopyresampled(
                $target->imgRes,//dest
                $this->imgRes,  //src
                0,0,            //dest x,y
                $startx, $starty,//src x,y 
                $width, $heigth, //dest w,h
                floor($width/$scale), floor($heigth/$scale)//src w,h
            );
            return $target;
        }
        else
        {
            return $this;
        }
    }
    
    /**
     * fixed size scale method 
     * @param int $width
     * @param int $heigth
     * @param color$color
     * @return WImage
     */
    public function fillscale($width, $heigth, $color)
    {
            //get x and y scale factor
        $oldWidth = imagesx($this->imgRes);
        $oldHeight = imagesy($this->imgRes);
        if($width != $oldWidth || $heigth != $oldHeight)
        {
            //scale
            $xscale = $width/$oldWidth;
            $yscale = $heigth/$oldHeight;
            $scale = ($xscale <= $yscale) ? $xscale : $yscale;
            
            //locate start offset in original file
            $startx = ceil(($width  - $oldWidth*$scale)/2);
            $starty = ceil(($heigth  - $oldHeight*$scale)/2);
            
            //create and copy crop
            $target = Image_GD::create($width, $heigth);
            $target->fill($color);
            $this->setAntialias($target->imgRes, true);
            imagecopyresampled(
                $target->imgRes,//dest
                $this->imgRes,  //src
                $startx , //dest x        
                $starty,//desty
                0,0,//src x,y 
                $oldWidth*$scale, $oldHeight*$scale, //dest w,h
                $oldWidth, $oldHeight//src w,h
            );
            return $target;
        }
        else
        {
            return $this;
        }
    }
    
    /**
     * var size scale method 
     * @param int $width
     * @param int $heigth
     * @param color$color
     * @return WImage
     */
    public function scaletofit($width, $heigth)
    {
                //get x and y scale factor
        $oldWidth = imagesx($this->imgRes);
        $oldHeight = imagesy($this->imgRes);
        if($width < $oldWidth || $heigth < $oldHeight)
        {
            //scale
            $xscale = $width/$oldWidth;
            $yscale = $heigth/$oldHeight;
            $scale = ($xscale <= $yscale) ? $xscale : $yscale;
            
            //locate start offset in original file
            $startx = ceil(($width  - $oldWidth*$scale));
            $starty = ceil(($heigth  - $oldHeight*$scale));
            
            //create and copy crop
            $target = Image_GD::create($width-$startx, $heigth-$starty);
            $this->setAntialias($target->imgRes, true);
            imagecopyresampled(
                $target->imgRes,//dest
                $this->imgRes,  //src
                0 , //dest x        
                0,//desty
                0,0,//src x,y 
                $oldWidth*$scale, $oldHeight*$scale, //dest w,h
                $oldWidth, $oldHeight//src w,h
            );
            return $target;
        }
        else
        {
            return $this;
        }
    }
    
    /**
     * fixed size scale method 
     * @param int $width
     * @param int $heigth
     * @return WImage
     */
    public function stretchscale($width, $heigth)
    {
        //get x and y scale factor
        $oldWidth = imagesx($this->imgRes);
        $oldHeight = imagesy($this->imgRes);
        if($width != $oldWidth || $heigth != $oldHeight)
        {
            //create and copy crop
            $target = Image_GD::create($width, $heigth);
            $this->setAntialias($target->imgRes, true);
            imagecopyresampled(
                $target->imgRes,//dest
                $this->imgRes,  //src
                0, 0,//dest x,y
                0, 0,//src x,y 
                $width,$heigth, //dest w,h
                $oldWidth, $oldHeight//src w,h
            );
            return $target;
        }
        else
        {
            return $this;
        }
    }
    
    //stretchscale
}
?>