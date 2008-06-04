<?php
/**
 * @package Bambus
 * @subpackage EventHandlers
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 25.04.2008
 * @license GNU General Public License 3
 */
interface HContentAccessEventHandler
{
	public function HandleContentAccessEvent(EContentAccessEvent $e);
}
?>