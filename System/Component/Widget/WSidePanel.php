<?php
/**
 * @package Bambus
 * @subpackage Widgets
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2008-11-13
 * @license GNU General Public License 3
 */
class WSidePanel extends BWidget 
{
    const NONE = 0;
    const CONTENT_LOOKUP = 1;
    const PROPERTY_EDIT = 2;
    const HELPER = 4;
    const MEDIA_LOOKUP = 8;
    const PERMISSIONS = 16;
    
    private $mode = self::NONE;
    private $mimetype;
    private $object = null;
    
	private $sidebarWidgets = array();
	
	//loads all components 
	public function __construct()
	{
	}

	public function setTargetContent(BContent $content)
	{
	    if(isset($this->object))
	    {
	        throw new XInvalidDataException('target object already set');
	    }
	    $this->object = $content;
	    $this->mimetype = $content->MimeType;
	}
	
	public function setTarget($name, $type)
	{
	    if(isset($this->object))
	    {
	        throw new XInvalidDataException('target object already set');
	    }	
	    $this->object = $name;
	    $this->mimetype = $type;
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
	    return isset($this->object);
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
		$ci = SComponentIndex::alloc()->init();
		$widgetNames = $ci->ImplementationsOf("ISidebarWidget");
		$widgets = array();
		foreach ($widgetNames as $v) 
		{
		    if(class_exists($v) 
		        && is_callable($v.'::isSupported') 
		        && call_user_func($v.'::isSupported', $this))
		    {
		        try{
		            $this->sidebarWidgets[$v] = new $v($this);
		        }catch(Exception $e){/*IGNORE WIDGET*/}
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
		$UAG = SUsersAndGroups::alloc()->init();
		if(RSent::has('WSidebar-selected') && in_array(RSent::get('WSidebar-selected'), $widgets))
		{
			$UAG->setMyPreference('WSidebar-selected', RSent::get('WSidebar-selected'));
		}
		$selected = $UAG->getMyPreference('WSidebar-selected');
		return (in_array($selected, $widgets)) ? $selected : $widgets[0];
	}

	/**
	 * @return string
	 */
	public function __toString()
	{
	    $html = '';
	    try{
	    $this->loadWidgets();
		
		if(count($this->sidebarWidgets) > 0)
		{
		    $selectedWidget = $this->selectWidget();
			ksort($this->sidebarWidgets, SORT_LOCALE_STRING);
			$html = "<div id=\"WSidebar\">\n<div class=\"side-scroll-body\">\n\t<div id=\"WSidebar-head\">\n";
			$html .= "\t\t<select name=\"WSidebar-selected\" onchange=\"org.bambuscms.wsidebar.show(this.options[this.selectedIndex].value);\">\n"; 
			//select
			foreach ($this->sidebarWidgets as $class => $object) 
			{
				$html .= sprintf(
					"\t\t\t<option value=\"%s\"%s>%s</option>\n"
					, $class
					,($class == $selectedWidget) ? ' selected="selected"' : ''
					, SLocalization::get($object->getName()));
			}
			
			$html .= "\t\t</select>\n\t</div>\n\t<div id=\"WSidebar-body\">\n";
			//widgets
			foreach ($this->sidebarWidgets as $class => $object) 
			{
				$html .= sprintf(
					"\t\t<div class=\"WSidebar-child\" id=\"WSidebar-child-%s\"%s>%s</div>\n"
					, $class
					,($class != $selectedWidget) ? ' style="display:none;"' : ''
					, strval($object));
			}
			
			$html .= "\t</div>\n</div>\n</div>";
		}
	    }catch (Exception $e)
	    {
	        echo $e->getTraceAsString();
	    }
		return $html;
	}
}
?>