<?php
/**
 * @package Bambus
 * @subpackage System
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 21.04.2008
 * @license GNU General Public License 3
 */
class SNotificationCenter extends BSystem implements IShareable
	,HContentChangedEventHandler ,HContentCreatedEventHandler 
	,HContentDeletedEventHandler ,HContentPublishedEventHandler 
	,HContentRevokedEventHandler 
{
	//utilize old NFC Class
	
	//IShareable
	const Class_Name = 'SNotificationCenter';
	public static $sharedInstance = NULL;
	private static $initializedInstance = false;
	
	/**
     * @return SNotificationCenter
     */
	public static function alloc()
	{
		$class = self::Class_Name;
		if(self::$sharedInstance == NULL && $class != NULL)
		{
			self::$sharedInstance = new $class();
		}
		return self::$sharedInstance;
	}
    /**
     * @return SNotificationCenter
     */
    public function init()
    {
    	return $this;
    }
	//end IShareable	
	
	//bambus 0.20 event handlers
	public function HandleContentChangedEvent(EContentChangedEvent $e)
	{
		$this->HandleEvent($e);
	}
	public function HandleContentCreatedEvent(EContentCreatedEvent $e)
	{
		$this->HandleEvent($e);
	}
	public function HandleContentDeletedEvent(EContentDeletedEvent $e)
	{
		$this->HandleEvent($e);
	}
	public function HandleContentPublishedEvent(EContentPublishedEvent $e)
	{
		$this->HandleEvent($e);
	}
	public function HandleContentRevokedEvent(EContentRevokedEvent $e)
	{
		$this->HandleEvent($e);
	}
	
	private function HandleEvent(BEvent $e)
	{
		$etype = get_class($e);
		$etype = substr($etype,strlen('EContent'));
		$etype = substr($etype,0,strlen('Event')*-1);
		$etype = str_replace('Changed', 'Saved', $etype);
		$NFC = NotificationCenter::alloc();
		$NFC->init();
		$NFC->report(($etype == 'Revoked') ? 'warning' : 'message', strtolower($etype), array());
	}
	
	public function report($type, $message)
	{
		$NFC = NotificationCenter::alloc();
		$NFC->init();
		$NFC->report($type, $message, array());
	}
	
	public function __toString()
	{
		$NFC = NotificationCenter::alloc();
        $NFC->init();
        $NFC->notifier();
	}
}
?>