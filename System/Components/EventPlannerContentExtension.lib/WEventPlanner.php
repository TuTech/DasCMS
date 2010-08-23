<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2009-06-11
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage Widget
 */
class WEventPlanner 
    extends BWidget 
   // implements ISidebarWidget
{
	private $targetObject = null;
	private static $retains = array();
	/**
	 * get an array of string of all supported classes 
	 * if it supports BObject, it supports all cms classes
	 * @return array
	 */
	public static function isSupported(WSidePanel $sidepanel)
	{
	    return (
	        $sidepanel->hasTarget()
	        && $sidepanel->isTargetObject()
	        && $sidepanel->isMode(WSidePanel::HELPER)
	    );
	}
	
	public function getName()
	{
	    return 'event_planner';
	}
	
	public function getIcon()
	{
	    return new WIcon('ics','',WIcon::SMALL,'mimetype');
	}
	
	public function processInputs()
	{
	}
	
	public function __construct(WSidePanel $sidepanel)
	{
		$this->targetObject = $sidepanel->getTarget();
	}
	
	public function __toString()
	{
	    ob_start();
	    $this->render();
	    $html = strval(ob_get_clean());
		return $html;
	}
	
	public function render()
	{
	    echo '<div id="WEventPlanner"><dl>',
	    '<dt><label>new schedule</label></dt>',
	    '<dd><dl><dt><label for="WEventPlanner_begin">Von</label></dt>',
	    '<dd><input type="text" id="WEventPlanner_begin" /></dd>',
	    '<dt><label for="WEventPlanner_end">Bis</label></dt>',
	    '<dd><input type="text" id="WEventPlanner_end" /></dd>',
	    '</dl><div id="WEventPlanner_button" onclick="org.bambuscms.weventplanner.scheduleEvent($(\'WEventPlanner_begin\').value, $(\'WEventPlanner_end\').value);">schedule event</div>',
	    '</dd><dt><label>scheduled events</label></dt><dd><ul id="WEventPlanner_Events"></ul></dd></dl>',
	    '</div>';
	}
	
	public function associatedJSObject()
	{
	    return 'org.bambuscms.weventplanner';
	}
}
?>