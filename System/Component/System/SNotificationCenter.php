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
	private static $sharedInstance = NULL;
	private static $initializedInstance = false;
	private static $notifications = array(); 
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
		self::report(($etype == 'Revoked') ? 'warning' : 'message', strtolower($etype), array());
	}
	
	private static function alertLog($type, $message)
	{
	    $tpl = new WTemplate('log_entry', WTemplate::SYSTEM);
	    $tpl->setEnvironment(array(
           'message' => $message
            ,'message_type' => $type
            ,'edit' => ''
            ,'user' => defined('BAMBUS_USER') ? constant('BAMBUS_USER') : ''
            ,'application' =>  defined('BAMBUS_APPLICATION') ? constant('BAMBUS_APPLICATION') : ''
            ,'timestamp' => time()
            ,'ip_address' => getenv ("REMOTE_ADDR")
            ,'cms_root' =>  defined('BAMBUS_CMS_ROOT') ? constant('BAMBUS_CMS_ROOT') : ''
            ,'working_dir' => getcwd()
            ,'seperator' => "\t"
            ,'attibutes' => ''
	    ));
        DFileSystem::Append(BAMBUS_CMS_ROOT.'/alerts.log', $tpl->render()."\n");    
	}
	
	public static function report($type, $message)
	{
	    if($type == 'alert')
	    {
	        self::alertLog($type, $message);
	    }
	    
	    //notifications to be sent on __toString()
        $msgid = crc32($type.$message);
	    if(array_key_exists($msgid, self::$notifications))
	    {
	        self::$notifications[crc32($type.$message)][2]++;
	    }
	    else
	    {
	       self::$notifications[crc32($type.$message)] = array($type, $message, 1);
	    } 
	}
	
	public function __toString()
	{
	    $msgs = '<div id="notifier"><div id="notifications">';
        foreach (self::$notifications as $ntf) 
        {
        	$msgs = sprintf(
                "%s<div class=\"%s\">%s</div>\n"
                ,$msgs
                ,$ntf[0]
                ,$ntf[1].($ntf[2] > 1 ? ' ('.$ntf[2].')':'')
            );
        }
        $msgs .= '</div></div>';
	    return $msgs; 
	}
}
?>