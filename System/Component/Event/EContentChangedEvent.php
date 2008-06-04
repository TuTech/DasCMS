<?php
/**
 * @package Bambus
 * @subpackage Events
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 28.11.2007
 * @license GNU General Public License 3
 */
class EContentChangedEvent extends BEvent
{
	public function __construct(BObject $sender, BContent $content)
	{
		$this->Sender = $sender;
		$this->Content = $content;
		parent::informHandlers($this);
	}
	/**
	 * @var BContent
	 */
	protected $Content;
}
?>