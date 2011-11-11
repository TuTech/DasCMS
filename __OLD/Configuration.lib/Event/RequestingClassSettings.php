<?php
class Event_RequestingClassSettings
	extends _Event
{
	public function __construct(Controller_Application_Configuration $sender)
	{
		$this->sender = $sender;
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
	    $this->sender->addSettings($section, get_class($object), $settings);
	}
}
?>
