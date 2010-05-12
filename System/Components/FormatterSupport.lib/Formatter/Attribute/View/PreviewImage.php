<?php
/**
 * @deprecated use View_Content_* instead
 */
class Formatter_Attribute_View_PreviewImage
    extends Formatter_Attribute_Options
    implements
        Interface_Formatter_Attribute_Linkable,
        Interface_Formatter_Attribute_OptionsSelectable,
        Interface_Formatter_Attribute_BackgroundStainable,
        Interface_Formatter_Attribute_Sizeable
{
    protected $persistentAttributes = array('linkTarget','selectedOption','backgroundColor','width','height');

    //Linkable
    protected $linkTarget = null;

    //BackgroundStainable
    protected $selectedOption = '0c';

    //OptionsSelectable
    protected $backgroundColor = '#ffffff';
    protected $options = array(
        '0c' => 'scale_aspect_to_fit_in_boundaries',
        '1c' => 'scale_aspect_and_crop',
        '1f' => 'scale_aspect_and_fill_background',
        '1s' => 'scale_by_stretch'
    );
    /*
    const MODE_SCALE_TO_MAX = 0;
    const MODE_FORCE = 1;

    const FORCE_BY_STRETCH = 's';
    const FORCE_BY_CROP = 'c';
    const FORCE_BY_FILL = 'f';*/

    //Sizeable
    protected $width = 200;
    protected $height = 150;

    //Sizeable
    public function getWidth()
    {
        return $this->width;
    }

    public function getHeight()
    {
        return $this->height;
    }

    public function setWidth($width)
    {
        $width = intval($width);
        if($width < 1)
        {
            throw new XArgumentException('with mus be a positive integer');
        }
        $this->width = $width;
    }

    public function setHeight($height)
    {
        $height = intval($height);
        if($height < 1)
        {
            throw new XArgumentException('with mus be a positive integer');
        }
        $this->height = $height;
    }


    //BackgroundStainable
    public function getBackgroundColor()
    {
        return $this->backgroundColor;
    }

    public function setBackgroundColor($color)
    {
        if(!preg_match('/^#([0-9a-f]{3}|[0-9a-f]{6})$/ui', $color))
        {
            throw new XArgumentException('given valus must be color formatted like #ffffff or #fff');
        }
        $this->backgroundColor = $color;
    }

    //Linkable
    public function getLinkAlias()
    {
        return $this->getContent()->getAlias();
    }

    public function setLinkingTarget($linkTarget)
    {
        $this->linkTarget = $linkTarget;
    }

    public function getLinkingTarget()
    {
    	return $this->linkTarget;
    }

    public function isLinkingEnabled()
    {
        return $this->linkTarget != null;
    }

    //_Formatter_Attribute
    protected function getFormatterClass()
    {
        return 'PreviewImage';
    }

    public function toXHTML($insertString = null)
    {
        $img = strval($this->getContent()->getPreviewImage()->scaled(
            $this->width,
            $this->height,
            substr($this->selectedOption,0,1),//mode
            substr($this->selectedOption,1,1),//method
            $this->backgroundColor
        ));
    	if($this->isLinkingEnabled()){
    		$img = $this->createLink($img);
    	}
        return parent::toXHTML($img."\n");
    }

	/**
     * @return VSpore
     */
    public function getTargetView()
    {
        return VSpore::byName($this->linkTarget);
    }
}
?>