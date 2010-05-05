<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2007-11-28
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage EventHandlers
 */
interface HContentPublishedEventHandler
{
	public function HandleContentPublishedEvent(EContentPublishedEvent $e);
}
?>