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
class WNotifications extends BWidget implements ISidebarWidget 
{
	private $targetObject = null;
	/**
	 * @return array
	 */
	public static function isSupported(WSidePanel $sidepanel)
	{
	    return true;
	}
	
	public function getName()
	{
	    return 'notifications';
	}
	
	public function getIcon()
	{
	    return new WIcon('notify','',WIcon::SMALL,'action');
	}
	
	public function processInputs()
	{
	}
	
	public function __construct(WSidePanel $sidepanel)
	{
	}
	
	public function __toString()
	{
		$html = '<div id="WNotifications">';
		$html .= sprintf(
			'<dl><dt><label>%s</label></dt><dd id="WNotifications-area"><p>%s</p></dd></dl>'
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