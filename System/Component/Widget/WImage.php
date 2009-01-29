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
    const MODE_FORCE_WITH = 1;
    const MODE_FORCE_HEIGTH = 2;
    const MODE_FORCE_BOTH = 3;
    
    const FORCE_BY_STRETCH = 's';
    const FORCE_BY_CROP = 'c';
    const FORCE_BY_FILL = 'f';
    
    private $width = null;
    private $height = null;
    /**
     * @var BContent
     */
    private $content = '';
    private $imageData;
    private $mode = '';
    private $forceType = '';
    private $fillColor = '';
    private $scaleHash = '0';
    private $imageID = '_';//default cms preview image
    
    /**
     * create image for output
     * @param IFileContent$content
     * @return unknown_type
     */
    public function __construct(BContent $content)
    {
        $this->content = $content;
        if ($content instanceof IFileContent) 
        {
            list($kind, $enc) = explode('/',strtolower($content->getMimeType()));
            if($kind == 'image' && in_array($enc, array('jpg','jpeg','png','gif')))
            {
                //render this image
                $this->imageID = 'c'.$content->getId();
            }
        }
       /* elseif ($content instanceof ISelectablePreviewImage)
        {
        ISelectablePreviewImage:
        -getPreviewImageID
        -setPreviewImageID
           //$this->imageID = 'p'.$c->getPreviewImageID() 
        }*/
    }
    
    /**
     * set scale method
     * @param int $width
     * @param int $heigth
     * @param int $mode MODE_SCALE_TO_MAX, MODE_FORCE_WITH, MODE_FORCE_HEIGTH or MODE_FORCE_BOTH
     * @param string $forceType FORCE_BY_STRETCH, FORCE_BY_CROP or FORCE_BY_FILL
     * @param string $fillColor 3 or 6 digit hex code (#136 or #123456)
     * @return WImage
     */
    public function scaled($width, $heigth, $mode = self::MODE_SCALE_TO_MAX, $forceType = self::FORCE_BY_FILL, $fillColor = '#ffffff')
    {
        $matches = array();
        //split 3 and 6 letter hex-color-codes into r,g and b 
        preg_match('/^#?(([0-9A-Fa-f]{2})([0-9A-Fa-f]{2})([0-9A-Fa-f]{2})|([0-9A-Fa-f])([0-9A-Fa-f])([0-9A-Fa-f]))$/',$fillColor,$matches);
        $r = $g = $b = 'ff';
        if($matches)
        {
            list($n, $il, $r,$g, $b) = $matches;
        }
        $this->scaleHash = sprintf(
            '%s-%x-%x-%1d-%1s-%s-%s-%s',
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
            "<img src=\"%s?key=%s\" alt=\"%s\" title=\"%s\" />"
            ,SPath::SYSTEM_IMAGES.'inet-180.jpg'//FIXME image renderer path here
            ,base64_encode($this->scaleHash)
            ,htmlentities($this->content->getTitle(), ENT_QUOTES, 'UTF-8')
            ,htmlentities($this->content->getTitle(), ENT_QUOTES, 'UTF-8')
        );
    }
}
?>