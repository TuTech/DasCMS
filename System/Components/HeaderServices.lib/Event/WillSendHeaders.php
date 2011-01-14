<?php
class Event_WillSendHeaders 
	extends _Event
{
	/**
	 * @param IHeaderAPI $sender
	 */
	public function __construct(IHeaderAPI $sender)
	{
		$this->sender = $sender;
		$this->informHandlers();
	}

	/**
	 * @return IHeaderAPI
	 */
	public function getHeader()
	{
	    return $this->sender;
	}
}
?>
