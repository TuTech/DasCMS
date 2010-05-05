<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2007-11-28
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage BaseClasses
 */
abstract class BView extends BObject
{
	/**
	 * is the header allowed to include meta data like 
	 * title, decription or tags of the content in this view
	 * @return boolean
	 */
	public function publishMetaData()
	{
	    return true;
	}
}
?>