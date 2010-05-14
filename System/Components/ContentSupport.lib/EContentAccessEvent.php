<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2008-04-25
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage Events
 */
class EContentAccessEvent extends BEvent
{
	public function __construct(BObject $sender, Interface_Content $content)
	{
		$this->Sender = $sender;
		$this->Content = $content;
		parent::informHandlers($this);
	}
	
	 /**
	 * @var Interface_Content
	 */
	protected $Content;
}
?>