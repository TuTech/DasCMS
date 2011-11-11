<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2008-11-13
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage Widget
 */
class View_UIElement_SidePanel
    extends
        _View_UIElement
    implements
        Interface_Singleton
{
    const NONE = 0;
    const CONTENT_LOOKUP = 1;
    const PROPERTY_EDIT = 2;
    const HELPER = 4;
    const MEDIA_LOOKUP = 8;
    const PERMISSIONS = 16;
    const WYSIWYG = 32;
    const RETAIN = 64;
    const INFORMATION = 128;

    private $mode = self::NONE;
    private $mimetype;
    private $object = null;
    private $enableAutoProcess = false;

	private $sidebarWidgets = array();
	private $inputsProcessed = false;
	//Interface_Singleton
	const CLASS_NAME = 'View_UIElement_SidePanel';
	/**
	 * @var View_UIElement_SidePanel
	 */
	public static $sharedInstance = NULL;

	/**
	 * @return View_UIElement_SidePanel
	 */
	public static function getInstance()
	{
		$class = self::CLASS_NAME;
		if(self::$sharedInstance == NULL && $class != NULL)
		{
			self::$sharedInstance = new $class();
		}
		return self::$sharedInstance;
	}
	//end Interface_Singleton
	//loads all components
	private function __construct()
	{
	}

	public function setTargetContent(Interface_Content $content)
	{
	    if(isset($this->object))
	    {
	        throw new InvalidDataException('target object already set');
	    }
	    $this->object = $content;
	    $this->mimetype = $content->getMimeType();
	    if($this->enableAutoProcess)
	    {
	        $this->processInputs();
	    }
	}

	public function setTarget($name, $type)
	{
	    if(isset($this->object))
	    {
	        throw new InvalidDataException('target object already set');
	    }
	    $this->object = $name;
	    $this->mimetype = $type;
	    if($this->enableAutoProcess)
	    {
	        $this->processInputs();
	    }
	}

	private function targetFail($failmessage)
	{
	    if(!isset($this->object))
	    {
	        throw new InvalidDataException($failmessage);
	    }
	}

	public function hasTarget()
	{
	    return $this->object !== null;
	}

	public function isTargetObject()
	{
	    $this->targetFail('target object not set');
	    return is_object($this->object);
	}

	public function getTarget()
	{
	    $this->targetFail('target object not set');
	    return $this->object;
	}

	public function getTargetMimeType()
	{
	    $this->targetFail('target object not set');
	    return $this->mimetype;
	}

	public function isMode($mode)
	{
	    return ($this->mode & $mode);
	}

	public function getMode()
	{
	    return $this->mode;
	}

	public function setMode($mode)
	{
	    return $this->mode = $mode;
	}

	private function loadWidgets()
	{
		//load all ISidebarWidget
		$widgetNames = Core::getClassesWithInterface("ISidebarWidget");
		$widgets = array();
		foreach ($widgetNames as $v)
		{
		    if(class_exists($v)
		        && is_callable($v.'::isSupported')
		        && call_user_func($v.'::isSupported', $this))
		    {
		        try{
		            $this->sidebarWidgets[$v] = new $v($this);
		        }catch(Exception $e){/*IGNORE WIDGET*/
		            SNotificationCenter::report('warning', 'could_not_load_widget_'.$v);
		        }
		    }
		}
	}

	public function hasWidgets()
	{
	    return count($this->sidebarWidgets) > 0;
	}

	public function setProcessMode($mode)
	{
	    switch($mode)
	    {
	        case 'now': $this->processInputs(); break;
	        case 'auto': $this->enableAutoProcess = true;break;
	    }
	}

	public function processInputs()
	{
	    if(!$this->inputsProcessed)
	    {
    	    $this->inputsProcessed = true;
	        //load target from app controller
	        if(!$this->hasTarget())
	        {
	            $dat = SApplication::appController()->getSideBarTarget();
	            if(count($dat) == 1)
	            {
	                $this->setTargetContent($dat[0]);
	            }
	            elseif (count($dat) == 2)
	            {
	                $this->setTarget($dat[0], $dat[1]);
	            }
	        }
    	    $this->loadWidgets();
    	    if(count($this->sidebarWidgets) > 0)
    		{
    		    foreach ($this->sidebarWidgets as $class => $object)
    		    {
    		        $object->processInputs();
    		    }
    		}
	    }
	}

	/**
	 * @return string
	 */
	public function __toString()
	{
	    $html = '';
		try
	    {
			if($this->enableAutoProcess)
			{
				$this->processInputs();
			}
    		if(count($this->sidebarWidgets) > 0)
    		{
    			ksort($this->sidebarWidgets, SORT_LOCALE_STRING);
    			$html = "";
    			foreach ($this->sidebarWidgets as $class => $object)
    			{
    				$html .= sprintf(
    					"<div class=\"document-options\">%s<h2>%s</h2>%s</div>\n",
						$object->getIcon(),
						SLocalization::get($object->getName()),
    					$object
					);
    			}
    		}
	    }
	    catch (Exception $e)
	    {
	        return strval($e);
	    }
		return $html;
	}
}
?>