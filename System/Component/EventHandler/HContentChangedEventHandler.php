<?php
/**
 * @package Bambus
 * @subpackage EventHandlers
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 28.11.2007
 * @license GNU General Public License 3
 */
interface HContentChangedEventHandler
{
	public function HandleContentChangedEvent(EContentChangedEvent $e);
}
?>