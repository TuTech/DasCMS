<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2008-04-25
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage EventHandlers
 */
interface HContentAccessEventHandler
{
	public function HandleContentAccessEvent(EContentAccessEvent $e);
}
?>