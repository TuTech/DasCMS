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
	 * get an array of string of all supported classes 
	 * if it supports BObject, it supports all cms classes
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
			'<h3>%s</h3><div id="WNotifications-area"><p>%s</p></div>'
		    , SLocalization::get('notifications')
		    , SLocalization::get('no_notifications')
		    );
		$html .= '</div>';
		return $html;
	}
}
?>