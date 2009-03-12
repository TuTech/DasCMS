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
    extends 
        BSystem 
    implements 
        IShareable,
    	HContentChangedEventHandler,
    	HContentCreatedEventHandler, 
    	HContentDeletedEventHandler,
    	HContentPublishedEventHandler, 
    	HContentRevokedEventHandler 
{
	//utilize old NFC Class
	
	//IShareable
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
	public static function alloc()
	{
		$class = self::CLASS_NAME;
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
            ,'user' => PAuthentication::getUserID()
            ,'application' =>  SApplication::alloc()->init()->getGUID()
            ,'timestamp' => time()
            ,'ip_address' => getenv ("REMOTE_ADDR")
            ,'cms_root' =>  defined('BAMBUS_CMS_ROOTDIR') ? constant('BAMBUS_CMS_ROOTDIR') : ''
            ,'working_dir' => getcwd()
            ,'seperator' => "\t"
            ,'attibutes' => ''
	    ));
        DFileSystem::Append(BAMBUS_CMS_ROOTDIR.'/alerts.log', $tpl->render()."\n");    
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