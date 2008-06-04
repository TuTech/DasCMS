<?php
/**
 * @package Bambus
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 13.12.2007
 * @license GNU General Public License 3
 */
interface IShareable
{
	//instanciate class if neccessary and return a link to the object
	public static function alloc();
	
	//tell object to establish links to other classes etc
	//return $this
	public function init();
}

?>