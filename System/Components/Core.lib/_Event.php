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

	public function __get($var)
	{
		if(isset($this->{$var}))
		{
			return $this->{$var};
		}
		return null;
	}

	public function __set($var, $value){}

	protected function informHandlers()
	{
		$className = substr(get_class($this),strlen('Event_'));
		$handler = sprintf("Event_Handler_%s", $className);
		$handleEvent = sprintf("handleEvent%s", substr($class,1));

		$listenerClasses = Core::getClassesWithInterface($handler);
		foreach ($listenerClasses as $eventListenerClass)
		{
		    if(Core::isImplementation($eventListenerClass, 'IShareable'))
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