<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2009-02-25
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage Widget
 */
class View_UIElement_Notifications extends _View_UIElement implements ISidebarWidget 
{
	private $targetObject = null;
	/**
	 * @return array
	 */
	public static function isSupported(View_UIElement_SidePanel $sidepanel)
	{
	    return true;
	}
	
	public function getName()
	{
	    return 'notifications';
	}
	
	public function getIcon()
	{
	    return new View_UIElement_Icon('notify','',View_UIElement_Icon::SMALL,'action');
	}
	
	public function processInputs()
	{
	}
	
	public function __construct(View_UIElement_SidePanel $sidepanel)
	{
	}
	
	public function __toString()
	{
		$html = '<div id="View_UIElement_Notifications">';
		$html .= sprintf(
			'<dl><dt><label>%s</label></dt><dd id="View_UIElement_Notifications-area"><p>%s</p></dd></dl>'
		    , SLocalization::get('notifications')
		    , SLocalization::get('no_notifications')
		    );
		$html .= '</div>';
		return $html;
	}
	
	public function associatedJSObject()
	{
	    return null;
	}
}
?>