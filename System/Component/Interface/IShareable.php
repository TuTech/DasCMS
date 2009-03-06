<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2008-12-13
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage Interface
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