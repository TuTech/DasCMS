<?php
/**
 * @package Bambus
 * @subpackage EventHandlers
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2008-10-31
 * @license GNU General Public License 3
 */
interface HWillAccessContentEventHandler
{
	public function HandleWillAccessContentEvent(EWillAccessContentEvent $e);
}
?>