<?php
/**
 * @package Bambus
 * @subpackage Widgets
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2009-01-29
 * @license GNU General Public License 3
 */
class WImage extends BWidget 
{
    const MODE_SCALE_TO_MAX = 0;
    const MODE_FORCE = 1;
    
    const FORCE_BY_STRETCH = 's';
    const FORCE_BY_CROP = 'c';
    const FORCE_BY_FILL = 'f';
    
    private static $retainCounts = null;
    
    private $width = null;
    private $height = null;
    /**
     * @var BContent
     */
    private $content = null;
    private $imageData;
    private $mode = '';
    private $alias = '';
    private $forceType = '';
    private $fillColor = '';
    private $scaleHash = '';
    private $imageID = '_';//default cms preview image
    private $allowsPreview = true;
    
    
    /**
     * create image for output
     * @param IFileContent$content
     * @return string
     */
    public static function getPreviewIdForContent(BContent $content)
    {
        if ($content instanceof IFileContent) 
        {
            if(self::supportedMimeType($content->getMimeType()))
            {
                //render this image
                $img = $content->getId();
                $img->allowsPreview = false;
            }
        }
        else
        {
            $res = QWImage::getPreviewAlias($content->getId());
            $img = '_';
            if($res->getRowCount() == 1)
            {
                list($pid) = $res->fetch(); 
                $img = $pid;
            }
            $res->free();
        }
        return $img;
    }  
      
    /**
     * create image for output
     * @param IFileContent$content
     * @return WImage
     */
    public static function forContent(BContent $content)
    {
        $img = new WImage();
        $img->content = $content;
        if ($content instanceof IFileContent) 
        {
            if(self::supportedMimeType($content->getMimeType()))
            {
                //render this image
                $img->imageID = $content->getId();
                $img->allowsPreview = false;
            }
        }
        else
        {
            $res = QWImage::getPreviewAlias($content->getId());
            if($res->getRowCount() == 1)
            {
                list($pid) = $res->fetch(); 
                $img->imageID = $pid;
            }
            $res->free();
        }
        return $img;
    }
    /**
     * returns 
     * 	a) the alias of the preview image
     * 	b) an empty string if no preview is set
     * 	c) null if preview can not be set
     * @return string
     */
    public function getAlias()
    {
        if($this->allowsPreview)
        {
            return self::resolvePreviewId($this->imageID);
        }
        else
        {
            return null;
        }
    }    
    public static function resolvePreviewId($id)
    {
        $alias = '';
        $res = QWImage::idToAlias($id);
        if($res->getRowCount())
        {
            list($alias) = $res->fetch();
        }
        $res->free();
        return $alias;
    }
    
    public static function setPreview($contentAlias, $previewAlias)
    {
        $res = QWImage::getpreviewId($previewAlias);
        //is the content assigned to $previewAlias a valid preview? (mimetype image/(jpe?g|png|gif)) 
        if($res->getRowCount())
        {
            list($pid) = $res->fetch();
            //link cid to pid
            QWImage::setPreview($contentAlias, $pid);
        }
        else
        {
            QWImage::removePreview($contentAlias);
        }
        $res->free();
    }
    
    public static function supportedMimeType($type)
    {
        list($kind, $enc) = explode('/',strtolower($type));
        return ($kind == 'image' && in_array($enc, array('jpg','jpeg','png','gif')));
    }
    
    public static function getRetainCounts()
    {
        if(self::$retainCounts == null)
        { 
            $res = QWImage::getRetainCounts();
            self::$retainCounts = array();
            while($row = $res->fetch())
            {
                self::$retainCounts[$row[0]] = $row[1];
            }
        }
        return self::$retainCounts;
    }
    
    public static function getAllPreviewContents()
    {
        //alias => title
        $ret = array();
        $res = QWImage::getPreviewContents();
        while($row = $res->fetch())
        {
            $ret[$row[0]] = $row[1];
        }
        return $ret;
    } 
    
    public static function forCFileData($id, $type, $alias, $title)
    {
        $img = new WImage();
        $img->alias = $alias;
        $img->title = $title;
        if(self::supportedMimeType($type))
        {
            $img->imageID = $id;
        }
        return $img;
    }
    
    /**
     * set scale method
     * @param int $width
     * @param int $heigth
     * @param int $mode MODE_SCALE_TO_MAX or MODE_FORCE
     * @param string $forceType FORCE_BY_STRETCH, FORCE_BY_CROP or FORCE_BY_FILL
     * @param string $fillColor 6 digit hex code #123456
     * @return WImage
     */
    public function scaled($width, $heigth, $mode = self::MODE_SCALE_TO_MAX, $forceType = self::FORCE_BY_FILL, $fillColor = '#ffffff')
    {
        $this->scaleHash = self::createScaleHash($width, $heigth, $mode, $forceType, $fillColor);
        //permit rendering this image
        if(!file_exists(SPath::TEMP.'scale.render.'.$this->imageID.'-'.$this->scaleHash))
        {
            touch(SPath::TEMP.'scale.permit.'.$this->imageID.'-'.$this->scaleHash);
        }
        return $this;
    }
    
    public static function createScaleHash($width, $heigth, $mode = self::MODE_SCALE_TO_MAX, $forceType = self::FORCE_BY_FILL, $fillColor = '#ffffff')
    {
        $matches = array();
        //split 3 and 6 letter hex-color-codes into r,g and b 
        preg_match('/^#?(([0-9A-Fa-f]{2})([0-9A-Fa-f]{2})([0-9A-Fa-f]{2}))$/',$fillColor,$matches);
        $r = $g = $b = 'ff';
        if($matches)
        {
            list($n, $full, $r,$g, $b) = $matches;
        }
        return sprintf(
            '%x-%x-%01d-%1s-%02s-%02s-%02s',
            $width,
            $heigth,
            $mode,
            $forceType,
            $r,$g,$b
        );
        
    }
    
    public function __toString()
    {
        return sprintf(
            "<img src=\"image.php/%s/%s\" alt=\"%s\" title=\"%s\" />"
            ,empty($this->content) ? $this->alias : $this->content->getAlias()//FIXME image renderer path here
            ,$this->scaleHash
            ,htmlentities(empty($this->content) ? $this->title : $this->content->getTitle(), ENT_QUOTES, 'UTF-8')
            ,htmlentities(empty($this->content) ? $this->title : $this->content->getTitle(), ENT_QUOTES, 'UTF-8')
        );
    }
}
?>