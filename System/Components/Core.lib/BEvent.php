<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2007-11-28
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage BaseClasses
 */
abstract class BEvent extends BObject
{
	/**
	 * @var BObject
	 */
	protected $Sender = null;
	protected $Canceled = false;
	
	public function Cancel()
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
	
	/**
	 * Invoke all classes implementing the handler-interface for the given event
	 *
	 * @param BEvent $e
	 */
	protected static function informHandlers(BEvent $e)
	{
		$class = get_class($e);
		//HContentChangedEventHandler
		$handler = sprintf("H%sHandler", substr($class,1));
		//HandleContentChangedEvent
		$handleEvent = sprintf("Handle%s", substr($class,1));
		
		$listenerClasses = Core::getClassesWithInterface($handler);
		foreach ($listenerClasses as $eventListenerClass) 
		{
		    if(Core::isImplementation($eventListenerClass, 'IShareable'))
		    {
		        $eventListener = call_user_func($eventListenerClass.'::getSharedInstance');
		    }
			else
			{
			    $eventListener = new $eventListenerClass();
			}
			if($eventListener instanceof $handler)
		    {
    			if(!method_exists($eventListener, $handleEvent))
    			{
    			    throw new Exception($eventListenerClass.' ['.get_class($eventListener).'::'.$handleEvent.'()]');
    			}
    			$eventListener->{$handleEvent}($e);
		    }
		    else
		    {
		        SNotificationCenter::report('warning', 'component index out of sync');
		    }
		}
	}
}
?>