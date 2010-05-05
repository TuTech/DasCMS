<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2007-11-28
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage Events
 */
class EContentPublishedEvent extends BEvent
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