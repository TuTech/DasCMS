<?php
/**
 * @package Bambus
 * @subpackage BaseClasses
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 28.11.2007
 * @license GNU General Public License 3
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
		
		$SCI = SComponentIndex::alloc()->init();
		$listenerClasses = $SCI->ImplementationsOf($handler);
		foreach ($listenerClasses as $eventListenerClass) 
		{
			$c = new $eventListenerClass();
			if($c instanceof $handler)
		    {
    			$eventListener = $c->alloc();//call_user_func_array(array($eventListenerClass, 'alloc'));
    			$eventListener->init();
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