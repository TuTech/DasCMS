<?php
class Event_RequestingClassSettings
	extends _Event
{
	/**
	 * @var Controller_Application_Configuration
	 */
	protected $Sender = null;
	public function __construct(Controller_Application_Configuration $sender)
	{
		$this->Sender = $sender;
		$this->informHandlers();
	}

	/**
	 * @param $object owner of the config keys
	 * @param string $section section for these keys
	 * @param array $settings key=>value
	 * @return void
	 */
	public function addClassSettings($object, $section, array $settings)
	{
	    $this->Sender->addSettings($section, get_class($object), $settings);
	}
}
?>
