<?php
class Event_ContentRevoked
	extends _EventContent
{
	public function __construct($sender, Interface_Content $content)
	{
		$this->sender = $sender;
		$this->content = $content;
		$this->informHandlers();
	}
}
?>