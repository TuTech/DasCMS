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
            }
        }
        $this->imageData = SPath::SYSTEM_IMAGES.'inet-180.jpg';
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
    public function scale($width, $heigth, $mode = self::MODE_SCALE_TO_MAX, $forceType = self::FORCE_BY_FILL, $fillColor = '#ffffff')
    {
        //build scale config string
        //send to db and get $id
        //link to render.php?file=:Alias&mode=$id
        $this->setSize($size);
        return $this;
    }
    
    public function __toString()
    {
        return sprintf(
            "<img src=\"%s\" alt=\"%s\" title=\"%s\" />"
            ,$this->imageData
            ,htmlentities($this->content->getTitle(), ENT_QUOTES, 'UTF-8')
            ,htmlentities($this->content->getTitle(), ENT_QUOTES, 'UTF-8')
        );
    }
}
?>