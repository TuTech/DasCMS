<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2008-03-19
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage Interface
 */
interface ISidebarWidget
{
    /**
     * @param View_UIElement_SidePanel $sidepanel
     */
    public function __construct(View_UIElement_SidePanel $sidepanel); 
    
    /**
     * @param View_UIElement_SidePanel $sidepanel
     * @return boolean
     */
    public static function isSupported(View_UIElement_SidePanel $sidepanel);
	
	/**
	 * @return string
	 */
	public function getName();
	
	/**
	 * @return View_UIElement_Icon
	 */
	public function getIcon();

	/**
	 * @return void
	 */
	public function processInputs();
	
	/**
	 * @return string|null
	 */
	public function associatedJSObject();
}
?>