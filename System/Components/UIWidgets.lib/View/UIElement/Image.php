<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2009-01-29
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage Widget
 */
class View_UIElement_Image extends _View_UIElement
{
	const CLASS_NAME = 'View_UIElement_Image';
    const MODE_SCALE_TO_MAX = 0;
    const MODE_FORCE = 1;

    const FORCE_BY_STRETCH = 's';
    const FORCE_BY_CROP = 'c';
    const FORCE_BY_FILL = 'f';

    private static $retainCounts = null;
	private static $placeholderImage = null;

    private $width = null;
    private $height = null;
    /**
     * @var Interface_Content
     */
    private $content = null;
    private $imageData;
    private $mode = '';
    private $alias = '';
    private $forceType = '';
    private $fillColor = '';
    private $scaleHash = '';
    private $noCacheAddOn = '';
    private $imageID = 0;//default cms preview image
    private $allowsPreview = true;

    private $isPreviewImage = false;
    private $cssID = null;
    private $isPlaceHolderImage = false;

    /**
     * @return View_UIElement_Image
     */
    public function asPreviewImage()
    {
        $this->isPreviewImage = true;
        return $this;
    }

    /**
     * @return View_UIElement_Image
     */
    public function asUncachedImage()
    {
    	$this->noCacheAddOn = '/v/'.sha1('nocache'.time());
    	return $this;
    }

    /**
     * @return View_UIElement_Image
     */
    public function asCachedImage()
    {
    	$this->noCacheAddOn = '';
    	return $this;
    }



    /**
     * create image for output
     * @param IFileContent$content
     * @return string
     */
    public static function getPreviewIdForContent(Interface_Content $content)
    {
        if ($content instanceof IFileContent && self::supportedMimeType($content->getMimeType()))
        {
            //render this image
            $img = $content->getId();
        }
        else
        {
            $img = self::resolvePreview($content->getAlias());
			if($img === null){
				$img = '_';
			}
        }
        return $img;
    }

    public function getCSSId()
    {
        return $this->cssID;
    }

    public function setCSSId($value)
    {
        $this->cssID = $value;
    }

    /**
     * create image for output
     * @param IFileContent$content
     * @return View_UIElement_Image
     */
    public static function forContent(Interface_Content $content)
    {
        $img = new View_UIElement_Image();
        $img->content = $content;
        if ($content instanceof IFileContent
				&& self::supportedMimeType($content->getMimeType()))
		{
                //render this image
                $img->imageID = $content->getId();
                $img->allowsPreview = false;
				$found = true;
        }
		else
        {
			$id = self::resolvePreview($content->getAlias());
			if($id !== null){
				$img->imageID = $id;
			}
			else{
				$img->isPlaceHolderImage = true;
			}
        }
        return $img;
    }

	private static function resolvePreview($alias){
		$id = null;
		$RelCtrl = Controller_ContentRelationManager::getInstance();
		$previews = $RelCtrl->getAllRetainedByContentAndClass($alias, self::CLASS_NAME);
		if(count($previews) > 0){
			$palias = array_pop($previews);
			$id = Core::Database()
				->createQueryForClass(self::CLASS_NAME)
				->call('aliasToId')
				->withParameters($palias)
				->fetchSingleValue();
		}
		return $id;
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
            $prev = self::resolvePreviewId($this->imageID);
			return !$prev ? '' : $prev;
        }
        else
        {
            return null;
        }
    }

    public static function resolvePreviewId($id)
    {
		$alias = Core::Database()
			->createQueryForClass(self::CLASS_NAME)
			->call('idToAlias')
			->withParameters($id)
			->fetchSingleValue();
        return $alias;
    }

    public static function setPreview($contentAlias, $previewAlias)
    {
		$DB = Core::Database()->createQueryForClass(self::CLASS_NAME);
		$RelCtrl = Controller_ContentRelationManager::getInstance();

		$palias = $DB->call('getPreviewId')
			->withParameters($previewAlias)
			->fetchSingleValue();

        //is the content assigned to $previewAlias a valid preview? (mimetype image/(jpe?g|png|gif))
        if(!empty ($palias))
        {
			$DB->beginTransaction();
			$RelCtrl->releaseAllRetainedByContentAndClass($contentAlias, self::CLASS_NAME);
			$RelCtrl->retain($previewAlias, $contentAlias, self::CLASS_NAME);
			$DB->commitTransaction();
        }
    }

    public static function supportedMimeType($type)
    {
        return in_array($type, self::getSupportedMimeTypes());
    }

    public static function getSupportedMimeTypes()
    {
        return array('image/jpg','image/jpeg','image/png','image/gif');
    }

    public static function getAllPreviewContents()
    {
		$alias = '';$title = '';
		$db = Core::Database()
			->createQueryForClass(self::CLASS_NAME)
			->call('getPreviewContents')
			->withoutParameters();

		$ret = array();
		while(list($alias, $title) = $db->fetchResult()){
			$ret[$alias] = $title;
		}
		return $ret;
    }

    public static function forCFileData($id, $type, $alias, $title)
    {
        $img = new View_UIElement_Image();
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
     * @return View_UIElement_Image
     */
    public function scaled($width, $heigth, $mode = self::MODE_SCALE_TO_MAX, $forceType = self::FORCE_BY_FILL, $fillColor = '#ffffff')
    {
        $this->scaleHash = self::createScaleHash($width, $heigth, $mode, $forceType, $fillColor);
        //permit rendering this image
        $qual = intval(Core::Settings()->getOrDefault('CFile_image_quality', 75));
        if(!file_exists(Core::PATH_TEMP.'scale.render.'.$qual.'.'.$this->imageID.'-'.$this->scaleHash)
            && !PAuthorisation::has('org.bambuscms.bcontent.previewimage.create') //does not need explicit permission
            )
        {
            //generate temporary permission file
            touch(Core::PATH_TEMP.'scale.permit.'.$this->imageID.'-'.$this->scaleHash);
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
    	$class = array();
    	if($this->isPreviewImage)$class[] = 'PreviewImage';
    	if($this->isPlaceHolderImage)$class[] = 'DefaultPreviewImage';
        return sprintf(
            "<img src=\"image.php/%s/%s%s\" class=\"%s\" alt=\"%s\" title=\"%s\" %s/>"
            ,empty($this->content) ? $this->alias : $this->content->getAlias()
            ,$this->scaleHash
            ,$this->noCacheAddOn
            ,implode(' ', $class)
            ,String::htmlEncode(empty($this->content) ? $this->title : $this->content->getTitle())
            ,String::htmlEncode(empty($this->content) ? $this->title : $this->content->getTitle())
            ,($this->cssID != null) ? 'id="'.String::htmlEncode($this->cssID).'" ' : ''
        );
    }

	public static function placeholderFile(){
		if(self::$placeholderImage === null){
			$img = 'no_preview.jpg';
			if(file_exists(Core::PATH_CONTENT.$img)){
				self::$placeholderImage = Core::PATH_CONTENT.$img;
			}
			else{
				self::$placeholderImage = Core::PATH_SYSTEM_IMAGES.$img;
			}
		}
		return self::$placeholderImage;
	}
}
?>