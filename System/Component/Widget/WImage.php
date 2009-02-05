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
    private $scaleHash = '0';
    private $imageID = '_';//default cms preview image
    
    /**
     * create image for output
     * @param IFileContent$content
     * @return unknown_type
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
                $img->imageID = 'c'.$content->getId();
            }
        }
        else
        {
            $res = QWImage::getPreviewAlias($content->getId());
            if($res->getRowCount() == 1)
            {
                list($pid) = $res->fetch(); 
                $img->imageID = 'p'.$pid;
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
        $type = substr($this->imageID,0,1);
        switch($type)
        {
            case 'p':
                return self::resolvePreviewId(substr($this->imageID,1));
            case '_':
                return '';
            case 'c':
            default:
                return null;
        }
    }    
    public static function resolvePreviewId($id)
    {
        $alias = null;
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
    
    public static function forCFileData($id, $type, $alias, $title)
    {
        $img = new WImage();
        $img->alias = $alias;
        $img->title = $title;
        if(self::supportedMimeType($type))
        {
            $img->imageID = 'c'.$id;
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
        $matches = array();
        //split 3 and 6 letter hex-color-codes into r,g and b 
        preg_match('/^#?(([0-9A-Fa-f]{2})([0-9A-Fa-f]{2})([0-9A-Fa-f]{2}))$/',$fillColor,$matches);
        $r = $g = $b = 'ff';
        if($matches)
        {
            list($n, $full, $r,$g, $b) = $matches;
        }
        $this->scaleHash = sprintf(
            '%s-%x-%x-%01d-%1s-%02s-%02s-%02s',
            $this->imageID,
            $width,
            $heigth,
            $mode,
            $forceType,
            $r,$g,$b
        );
        //permit rendering this image
        if(!file_exists(SPath::TEMP.'scale.render.'.$this->scaleHash))
        {
            touch(SPath::TEMP.'scale.permit.'.$this->scaleHash);
        }
        return $this;
    }
    
    public function __toString()
    {
        return sprintf(
            "<img src=\"image.php/%s/%s\" alt=\"%s\" title=\"%s\" />"
            ,empty($this->content) ? $this->alias : $this->content->getAlias()//FIXME image renderer path here
            ,base64_encode($this->scaleHash)
            ,htmlentities(empty($this->content) ? $this->title : $this->content->getTitle(), ENT_QUOTES, 'UTF-8')
            ,htmlentities(empty($this->content) ? $this->title : $this->content->getTitle(), ENT_QUOTES, 'UTF-8')
        );
    }
}
?>