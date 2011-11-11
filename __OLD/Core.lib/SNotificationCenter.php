<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2008-04-21
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage System
 */
class SNotificationCenter 
    implements 
        Interface_Singleton,
    	Event_Handler_ContentChanged,
    	Event_Handler_ContentCreated,
    	Event_Handler_ContentDeleted,
    	Event_Handler_ContentPublished,
    	Event_Handler_ContentRevoked
{
	//utilize old NFC Class
	
	//Interface_Singleton
	const CLASS_NAME = 'SNotificationCenter';
	const TYPE_WARNING = 'warning';
	const TYPE_MESSAGE = 'message';
	const TYPE_ALERT = 'alert';
	private static $sharedInstance = NULL;
	private static $initializedInstance = false;
	private static $notifications = array(); 
	/**
     * @return SNotificationCenter
     */
	public static function getInstance()
	{
		$class = self::CLASS_NAME;
		if(self::$sharedInstance == NULL && $class != NULL)
		{
			self::$sharedInstance = new $class();
		}
		return self::$sharedInstance;
	}
	//end Interface_Singleton	
	
	//bambus 0.20 event handlers
	public function handleEventContentChanged(Event_ContentChanged $e)
	{
		$this->handleEvent($e);
	}
	public function handleEventContentCreated(Event_ContentCreated $e)
	{
		$this->handleEvent($e);
	}
	public function handleEventContentDeleted(Event_ContentDeleted $e)
	{
		$this->handleEvent($e);
	}
	public function handleEventContentPublished(Event_ContentPublished $e)
	{
		$this->handleEvent($e);
	}
	public function handleEventContentRevoked(Event_ContentRevoked $e)
	{
		$this->handleEvent($e);
	}
	
	private function handleEvent(_Event $e)
	{
		$etype = get_class($e);
		self::report('message', $etype, array());
	}
	
	public static function report($type, $message)
	{
		if($type == 'alert' || $type == 'warning'){
			Core::Logger()->log($message, $type == 'warning' ? LOG_WARNING : LOG_ERR);
		}
		
	    //notifications to be sent on __toString()
        $msgid = crc32($type.$message);
	    if(array_key_exists($msgid, self::$notifications))
	    {
	        self::$notifications[$msgid][2]++;
	    }
	    else
	    {
	       self::$notifications[$msgid] = array($type, $message, 1);
	    } 
	}
	
	public function __toString()
	{
	    $html = '<div id="notifier">%s</div>';
	    $msgs = '<div id="notifications">';
        foreach (self::$notifications as $ntf) 
        {
        	$msgs = sprintf(
                "%s<div class=\"%s\">%s</div>\n"
                ,$msgs
                ,$ntf[0]
                ,SLocalization::get($ntf[1]).($ntf[2] > 1 ? ' ('.$ntf[2].')':'')
            );
        }
        $msgs .= '</div>';
        return (count(self::$notifications) > 0) ? sprintf($html, $msgs) : ''; 
	}
}
?>