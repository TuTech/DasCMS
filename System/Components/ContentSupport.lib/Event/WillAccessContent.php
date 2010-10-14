<?php
class Event_WillAccessContent
	extends _Event
{
	/**
	 * @var Interface_Content
	 */
	protected $Content;
	private $contentSubstituted = false;

	public function __construct($sender, Interface_Content $content)
	{
		$this->Sender = $sender;
		$this->Content = $content;
		$this->informHandlers();
	}

	public function substitute(Interface_Content $content)
	{
	    $this->Content = $content;
	    $this->contentSubstituted = true;
	}

	public function hasContentBeenSubstituted()
	{
	    return $this->contentSubstituted;
	}
}
?>