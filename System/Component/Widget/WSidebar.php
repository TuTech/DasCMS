<?php
/**
 * @package Bambus
 * @subpackage Widgets
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 01.04.2008
 * @license GNU General Public License 3
 */
class WSidebar extends BWidget 
{
	/**
	 * @var BObject
	 */
	public $target = null;
	private $sidebarWidgets = array();
	private $widgetNames = array();
	//loads all components 
	public function __construct($target)
	{
		//load all ISidebarWidget
		$ci = SComponentIndex::alloc()->init();
		if($target != null && $target instanceof ISupportsSidebar)
		{
			$this->target = $target;
		}
		try{
			$ci = SComponentIndex::alloc()->init();
			$this->widgetNames = $ci->ImplementationsOf("ISidebarWidget");
			$widgets = array();
			foreach ($this->widgetNames as $k => $v) 
			{
				$widgets[$k] = new $v($this->target);
			}
			$this->widgetNames = array();
			foreach ($widgets as $wdgt) 
			{
				if($wdgt->supportsObject($this->target)
					&&(
						$this->target === null
						|| $this->target->wantsWidgetsOfCategory($wdgt->getCategory()))
					)
				{
					$this->sidebarWidgets[$wdgt->getName()] = $wdgt;
					$this->widgetNames[] = get_class($wdgt);
				}
			}
		}
		catch(Exception $e){}
	}
	
	private function selectWidget(array $widgets)
	{
		//@todo remove chimera bindings
		global $_POST;
		if(count($widgets) == 0)
		{
			return '';
		}
		$UAG = SUsersAndGroups::alloc()->init();
		if(isset($_POST['WSidebar-selected']) && in_array($_POST['WSidebar-selected'], $widgets))
		{
			$UAG->setMyPreference('WSidebar-selected', $_POST['WSidebar-selected']);
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
		$selectedWidget = $this->selectWidget(array_values($this->widgetNames));
		
		if(count($this->sidebarWidgets) > 0)
		{
			ksort($this->sidebarWidgets, SORT_LOCALE_STRING);
			$html = "<div id=\"WSidebar\">\n<div class=\"side-scroll-body\">\n\t<div id=\"WSidebar-head\">\n";
			$html .= "\t\t<select name=\"WSidebar-selected\" onchange=\"WSidebarShow(this.options[this.selectedIndex].value);\">\n"; 
			//select
			foreach ($this->sidebarWidgets as $name => $object) 
			{
				$class = get_class($object);
				$html .= sprintf(
					"\t\t\t<option value=\"%s\"%s>%s</option>\n"
					, $class
					,($class == $selectedWidget) ? ' selected="selected"' : ''
					, htmlentities($name));
			}
			
			$html .= "\t\t</select>\n\t</div>\n\t<div id=\"WSidebar-body\">\n";
			//widgets
			foreach ($this->sidebarWidgets as $name => $object) 
			{
				$class = get_class($object);
				$html .= sprintf(
					"\t\t<div class=\"WSidebar-child\" id=\"WSidebar-child-%s\"%s>%s</div>\n"
					, $class
					,($class != $selectedWidget) ? ' style="display:none;"' : ''
					, strval($object));
			}
			
			$html .= "\t</div>\n</div>\n</div>";
		}
			//get all ISidebarWidgets
			//ask widget if it wants the target (type)
			//ask target if it wants the widget (cat)
			//if widgets > 0
				//build select with translated names of the widgets (A->Z sorted)
				//generate all widgets
				
		return $html;
	}
}
?>