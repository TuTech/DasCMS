<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2008-04-28
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage BaseClasses
 */
abstract class _View_UIElement
{
	protected static $CurrentWidgetID = 0;
	
	/**
	 * return rendered html
	 * @return string
	 */
	abstract public function __toString();
	
	/**
	 * process inputs etc
	 *
	 */
	public function run(){} 
	
	/**
	 * echo html 
	 */
	public function render()
	{
		echo $this->__toString();
	}
	/**
	 * return ID of primary editable element or null 
	 *
	 * @return string|null
	 */
	public function getPrimaryInputID()
	{
		return null;
	}
}
?>