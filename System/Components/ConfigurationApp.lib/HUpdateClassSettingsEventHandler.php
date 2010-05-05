<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2009-05-12
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage EventHandlers
 */
interface HUpdateClassSettingsEventHandler
{
	public function HandleUpdateClassSettingsEvent(EUpdateClassSettingsEvent $e);
}
?>