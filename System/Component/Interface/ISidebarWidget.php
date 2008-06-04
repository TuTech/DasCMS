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
	 * get category of this widget
	 * @return string
	 */
	public function getCategory();
	
	/**
	 * @return string
	 */
	public function getName();
	/**
	 * get an array of string of all supported classes 
	 * if it supports BObject, it supports all cms classes
	 * @return array
	 */
	public function supportsObject($object);
}
?>