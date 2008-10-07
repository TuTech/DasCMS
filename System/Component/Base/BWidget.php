<?php
/**
 * @package Bambus
 * @subpackage BaseClasses
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 28.04.2008
 * @license GNU General Public License 3
 */
abstract class BWidget extends BObject
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