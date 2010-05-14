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
class WSidePanel
    extends
        BWidget
    implements
        IShareable
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
	//IShareable
	const CLASS_NAME = 'WSidePanel';
	/**
	 * @var WSidePanel
	 */
	public static $sharedInstance = NULL;
	private static $initializedInstance = false;
	/**
	 * @return WSidePanel
	 */
	public static function getSharedInstance()
	{
		$class = self::CLASS_NAME;
		if(self::$sharedInstance == NULL && $class != NULL)
		{
			self::$sharedInstance = new $class();
		}
		return self::$sharedInstance;
	}
	//end IShareable
	//loads all components
	private function __construct()
	{
	}

	public function setTargetContent(Interface_Content $content)
	{
	    if(isset($this->object))
	    {
	        throw new XInvalidDataException('target object already set');
	    }
	    $this->object = $content;
	    $this->mimetype = $content->MimeType;
	    if($this->enableAutoProcess)
	    {
	        $this->processInputs();
	    }
	}

	public function setTarget($name, $type)
	{
	    if(isset($this->object))
	    {
	        throw new XInvalidDataException('target object already set');
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
	        throw new XInvalidDataException($failmessage);
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

	private function selectWidget()
	{
	    $widgets = array_keys($this->sidebarWidgets);
		if(count($widgets) == 0)
		{
			return '';
		}
		if(RSent::has('WSidebar-selected') && in_array(RSent::get('WSidebar-selected'), $widgets))
		{
		    RSession::set('WSidebar-selected', RSent::get('WSidebar-selected'));
		}
		$selected = RSession::get('WSidebar-selected');
		return (in_array($selected, $widgets)) ? $selected : $widgets[0];
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
	    if($this->enableAutoProcess)
	    {
	        $this->processInputs();
	    }
	    try
	    {
    		if(count($this->sidebarWidgets) > 0)
    		{
    		    $selectedWidget = $this->selectWidget();
    			ksort($this->sidebarWidgets, SORT_LOCALE_STRING);
    			$html = "<div id=\"WSidebar-head\">\n";
    			//select
    			$html .= sprintf('<input type="hidden" name="WSidebar-selected" id="WSidebar-selected" value="" />',htmlentities($selectedWidget));
    			$html .= '<table id="WSidebar-select"><tr>'."\n";
    			foreach ($this->sidebarWidgets as $class => $object)
    			{
    		        $html .=  sprintf(
    		        	"<td class=\"tab\"><a onmousedown=\"return org.bambuscms.wsidebar.show('%s'%s)\" href=\"javascript:nil();\" id=\"WSidebar-selector-%s\" title=\"%s\"%s>%s</a></td>\n"
    		        	,$class
    		        	,$object->associatedJSObject() == null ? '' : ", '".$object->associatedJSObject()."'"
    		        	,$class
    		        	,SLocalization::get($object->getName())
    		        	,($class == $selectedWidget) ? ' class="selectedWidget"' : ''
    		        	,$object->getIcon()
    		        );
    			}

    			$html .= '<td class="spacer"></td></tr></table>';
    			$html .= "\n\t</div>\n<div id=\"WSidebar\">\n\t<div class=\"side-scroll-body\">\n\t<div id=\"WSidebar-body\">\n";
    			//widgets
    			foreach ($this->sidebarWidgets as $class => $object)
    			{
    				$html .= sprintf(
    					"\t\t<div class=\"WSidebar-child\" id=\"WSidebar-child-%s\"%s>%s</div>\n"
    					, $class
    					,($class != $selectedWidget) ? ' style="display:none;"' : ''
    					, strval($object));
    			}
    			//show event for corresponding js-object
			    $html .= sprintf(
			    	'<script type="text/javascript">org.bambuscms.wsidebar.show(\'%s\'%s)</script>'
			        ,$selectedWidget
			        ,$this->sidebarWidgets[$selectedWidget]->associatedJSObject() == null ? '' : ", '".$this->sidebarWidgets[$selectedWidget]->associatedJSObject()."'"
		        );
    			$html .= "\t</div>\n</div>\n</div>";
    		}
	    }
	    catch (Exception $e)
	    {
	        echo $e->getTraceAsString();
	    }
		return $html;
	}
}
?>