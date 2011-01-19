<?php
class Event_PermissionsChanged
	extends _Event
{
	/**
	 * @param object $sender
	 */
	public function __construct($sender)
	{
		$this->sender = $sender;
		$this->informHandlers();
	}
}
?>
