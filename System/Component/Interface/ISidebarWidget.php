<?php
/**
 * @package Bambus
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 19.03.2008
 * @license GNU General Public License 3
 */
interface ISidebarWidget
{
    /**
     * @param WSidePanel $sidepanel
     */
    public function __construct(WSidePanel $sidepanel); 
    
    /**
     * @param WSidePanel $sidepanel
     * @return boolean
     */
    public static function isSupported(WSidePanel $sidepanel);
	/**
	 * get category of this widget
	 * @return string
	 *
	public function getCategory();
	
	/**
	 * @return string
	 */
	public function getName();
	/**
	 * @return WIcon
	 */
	public function getIcon();
}
?>