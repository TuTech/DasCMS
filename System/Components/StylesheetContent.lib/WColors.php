<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2009-02-26
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage Widget
 */
class WColors extends BWidget implements ISidebarWidget 
{
	private $targetObject = null;
	/**
	 * get an array of string of all supported classes 
	 * if it supports object, it supports all cms classes
	 * @return array
	 */
	public static function isSupported(WSidePanel $sidepanel)
	{
	    return ($sidepanel->hasTarget() && $sidepanel->getTargetMimeType() == 'text/css');
	}
	
	public function getName()
	{
	    return 'colors';
	}
	
	public function getIcon()
	{
	    return new WIcon('pickcolor','',WIcon::SMALL,'action');
	}
	
	public function processInputs()
	{
	}
	
	public function __construct(WSidePanel $sidepanel)
	{
	}
	
	public function __toString()
	{
		$html = '<div id="WColors">';
		$html .= sprintf(
			'<h3>%s</h3><div id="WColors-area"></div>'
		    , SLocalization::get('colors')
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