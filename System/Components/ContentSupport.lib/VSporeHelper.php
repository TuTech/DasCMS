<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2008-10-10
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage View
 */
class VSporeHelper
    extends
        BView
    implements
        ITemplateSupporter,
        IGlobalUniqueId
{
    const GUID = 'org.bambuscms.view.spore';

    public function getClassGUID()
    {
        return self::GUID;
    }

    private static $spores = array();

    private static $functions = array(
        'content' => array('view'),
        'description' => array('view'),
    	'title' => array('view'),
        'subtitle' => array('view'),
    	'pubdate' => array('view'),
        'author' => array('view'),
        'tags' => array('view'),
        'previewimage' => array('view', 'width', 'height', 'scale'),
        'type' => array('view'),
    	'property' => array('view', 'use'),
    	'altercontent' => array('view', 'alias'),
        'formatter' => array('view','use')
    );

    public function __construct()
    {
    }

    /**
     * get spore for name
     *
     * @param string $name
     * @return Interface_Content
     */
    private function sporeContent($name)
    {
        if(!VSpore::exists($name) || !VSpore::isActive($name))
        {
            throw new XUndefinedIndexException('spore not found: '.$name);
        }
        if(!array_key_exists($name, self::$spores))
        {
            $s = new VSpore($name);
            self::$spores[$name] = $s->getContent();
        }
        return self::$spores[$name];
    }

    private function altercontent($spore, $alias)
    {
        if(!VSpore::exists($spore) || !VSpore::isActive($spore))
        {
            throw new XUndefinedIndexException('spore not found: '.$spore);
        }
        try{
            self::$spores[$spore] = Controller_Content::getInstance()->accessContent($alias, $this);
            $s = new VSpore($spore);
            self::$spores[$spore]->setParentView($s);
        }
        catch (Exception $e)
        {
            echo $e;
        }
    }

    private function formatter($spore, $formatter)
    {
        try{
            return Controller_View::getInstance()->display($this->sporeContent($spore), $formatter);
        }catch (Exception $e)
        {
            return $e->getTraceAsString();
        }
    }

    private function type($spore)
    {
        return get_class($this->sporeContent($spore));
    }

    private function content($spore)
    {
        $c = $this->sporeContent($spore);
		$formatter = Core::settings()->get('Settings_ContentView_defaultContentView');
		if($formatter){
			$content = Controller_View::getInstance()->display($c, $formatter);
		}
		else{
			$content = $c->getContent();
		}
		return strval($content);
    }

    private function description($spore)
    {
        return $this->sporeContent($spore)->getDescription();
    }

    private function title($spore)
    {
        return $this->sporeContent($spore)->getTitle();
    }

    private function subtitle($spore)
    {
        return $this->sporeContent($spore)->getSubTitle();
    }

    private function pubdate($spore)
    {
        return $this->sporeContent($spore)->getPubDate();
    }

    private function previewimage($spore, $width, $height, $scale, $color)
    {
        $img = $this->sporeContent($spore)->getPreviewImage()->asPreviewImage();
        if($width > 0 && $height > 0 && is_numeric($width) && is_numeric($height))
        {
            $mode = View_UIElement_Image::MODE_FORCE;
            $force = View_UIElement_Image::FORCE_BY_CROP;

            switch($scale)
            {
                case 'aspect_fit':
                    $mode = View_UIElement_Image::MODE_SCALE_TO_MAX;break;
                case 'aspect_crop':
                    $force = View_UIElement_Image::FORCE_BY_CROP;break;
                case 'aspect_fill':
                    $force = View_UIElement_Image::FORCE_BY_FILL;break;
                case 'stretch':
                    $force = View_UIElement_Image::FORCE_BY_STRETCH;break;
            }
            $img = $img->scaled($width, $height,$mode, $force, $color);
        }
        $img->setCSSId('_'.$this->sporeContent($spore)->getGUID().'_previewimage');
        return $img;
    }

    private function author($spore)
    {
        return $this->sporeContent($spore)->Author;
    }

    private function tags($spore)
    {
        return implode(', ',$this->sporeContent($spore)->getTags());
    }

    private function property($spore, $propname)
    {
        try{
            $s = $this->sporeContent($spore);
            return $s->__get($propname);
        }catch (Exception $e){return '';}
    }

    /**
     * return an array with function => array(0..n => parameters [, 'description' =>  desc])
     *
     * @return array
     */
    public function templateProvidedFunctions()
    {
        return self::$functions;
    }

    /**
     * return an array with attributeName => description
     *
     * @return array
     */
    public function templateProvidedAttributes()
    {
        return array();
    }

    /**
	 * @param string $function
	 * @return boolean
	 */
	public function templateCallable($function)
	{
	    return in_array($function, array_keys(self::$functions));
	}

	/**
	 * @param string $function
	 * @param array $namedParameters
	 * @return string in utf-8
	 */
	public function templateCall($function, array $namedParameters)
	{
	    if(!$this->templateCallable($function))
	    {
	        throw new XTemplateException('called undefined function');
	    }
	    if(!array_key_exists('view', $namedParameters))
	    {
	        throw new XArgumentException('view must be defined');
	    }
	    if($function == 'property' && !array_key_exists('use', $namedParameters))
	    {
	        throw new XArgumentException('property name not defined');
	    }
	    switch ($function)
	    {
	        case 'formatter':
	            return $this->{$function}($namedParameters['view'],$namedParameters['use']);
	        case 'property':
	            return $this->{$function}($namedParameters['view'],$namedParameters['use']);
            case 'altercontent':
	            return $this->{$function}($namedParameters['view'],$namedParameters['alias']);
            case 'previewimage':
	            foreach(array('width', 'height', 'scale', 'color') as $p)
	            {
	                if(!isset($namedParameters[$p]))
	                    $namedParameters[$p] = null;
	            }
	            return $this->{$function}($namedParameters['view'],$namedParameters['width'],$namedParameters['height'],$namedParameters['scale'],$namedParameters['color']);
	        default:
	            return $this->{$function}($namedParameters['view']);
	    }
	}

	/**
	 * @param string $property
	 * @return string in utf-8
	 */
	public function templateGet($property)
	{
	    return '';
	}
}
?>