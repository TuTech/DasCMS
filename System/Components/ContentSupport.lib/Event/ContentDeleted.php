<?php
class Event_ContentDeleted
	extends _EventContent
{
	protected $guid;

	public function __construct($sender, $guid)
	{
		$this->sender = $sender;
		$this->guid = $guid;
		$this->informHandlers();
	}

	public function getContent() {
		return null;
	}
}
?>