<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2010-10-06
 * @license GNU General Public License 3
 */
abstract class _Event
{
	protected $Sender = null;
	protected $Canceled = false;

	public function cancel()
	{
		$this->Canceled = true;
	}

	public function isCanceled()
	{
		return $this->Canceled;
	}

	public function getSender()
	{
		return $this->Sender;
	}

	protected function informHandlers()
	{
		$className = substr(get_class($this),strlen('Event_'));
		$handler = sprintf("Event_Handler_%s", $className);
		$handleEvent = sprintf("handleEvent%s", $className);

		$listenerClasses = Core::getClassesWithInterface($handler);
		foreach ($listenerClasses as $eventListenerClass)
		{
		    if(Core::isImplementation($eventListenerClass, 'Interface_Singleton'))
		    {
		        $eventListener = call_user_func($eventListenerClass.'::getInstance');
		    }
			else
			{
			    $eventListener = new $eventListenerClass();
			}
			if($eventListener instanceof $handler)
		    {
    			$eventListener->{$handleEvent}($this);
		    }
		}
	}
}
?>