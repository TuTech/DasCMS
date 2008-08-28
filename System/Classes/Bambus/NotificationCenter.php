<?php
/**
 * @package Bambus
 * @subpackage Deprecated 
 * @deprecated 
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 12.03.2007
 * @license GNU General Public License 3
 */
class NotificationCenter extends Bambus implements IShareable
{
	//IShareable
	const Class_Name = 'NotificationCenter';
	public static $sharedInstance = NULL;
	private static $initializedInstance = false;
	public static function alloc()
	{
		$class = self::Class_Name;
		if(self::$sharedInstance == NULL && $class != NULL)
		{
			self::$sharedInstance = new $class();
		}
		return self::$sharedInstance;
	}
    
    function init()
    {
    	if(!self::$initializedInstance)
    	{
    		if(defined('BAMBUS_DEBUG'))printf("\n<!--[%s init]-->\n", self::Class_Name);
	    	self::$initializedInstance = true;
			$this->Configuration = Configuration::alloc();
			$this->FileSystem = FileSystem::alloc();
			$this->Template = Template::alloc();

			$this->Configuration->init();
			$this->FileSystem->init();
			$this->Template->init();
    	}
    }
	//end IShareable

	var $logTreshold = 4; //every message with importance > 2 will be written to the log
	var $notifications = array();
	var $notificationTypeImportance = array('alert' => 10, 'warning' => 8, 'message' => 6, 'information' => 1);
	var $notificationHTML = "<div class=\"%s\">%s</div>\n";//BambusNotification NotificationType
	var $template = false;
	
	function __construct()
	{
		parent::Bambus();
	}
    public function __sleep()
    {
    	//do not serialize
    	return array();
    }
    	
    
	
	function report($type, $notificationKey, $attributes, $from = '')
	{
		if($type == 'alert')
		{
			$att = '';
			if(is_array($attributes))
			{
				foreach($attributes as  $key => $value)
				{
					$att .= sprintf("\n\t[%s] => %s", $key, $value);
				}
				$att .= "\n";
			}
			else
			{
				$att = $attributes;
			} 
			printf(
				"\n<!--(%s) [%s]: %s %s-->\n"
				,get_class($this)
				,$type
				,$notificationKey
				,$att
			);
		}
		if($this->template == false)
		{
			$this->template = $this->FileSystem->read(parent::pathToFile('systemTemplate','log_entry'));
		}
		$type = strtolower($type);
		$notificationKey = strtolower($notificationKey);
		$autoAttributes = array(
			'message' => $notificationKey
			,'message_type' => $type
			,'edit' => ''
			,'user' => defined('BAMBUS_USER') ? constant('BAMBUS_USER') : ''
			,'application' =>  defined('BAMBUS_APPLICATION') ? constant('BAMBUS_APPLICATION') : ''
			,'timestamp' => time()
			,'ip_address' => getenv ("REMOTE_ADDR")
			,'cms_root' =>  defined('BAMBUS_CMS_ROOT') ? constant('BAMBUS_CMS_ROOT') : ''
			,'working_dir' => getcwd()
			,'seperator' => "\t"
			,'attibutes' => @implode(', ',$attributes)
		);
		foreach($autoAttributes as $key => $value)
		{
			if(empty($attributes[$key]))$attributes[$key] = $value;
		}
		if($this->Configuration->get('logChanges'))
		{
			$logEntry = str_replace("\n", " ", $this->Template->parse($this->template, $attributes, 'string'));
			$logFile = $this->pathToFile('changeLog');
			$this->FileSystem->writeLine(BAMBUS_CMS_ROOT.'/'.$logFile, $logEntry);
		}
		$n = &$this->notifications;
		if(!isset($n[$type])) $n[$type] = array();
		//add notification
		$n[$type][] = array($notificationKey, $attributes, $from);
		
	}
	
	function notifyUser()
	{
		$n = &$this->notifications;
		$nhtml = &$this->notificationHTML;
		$types = array_keys($n);
		sort($types, SORT_STRING);
		$result = array();
		$html = '';
		foreach($types as $type)
		{
			if(!isset($result[$type])) $result[$type] = array();
			//add them up
			foreach($n[$type] as $notification)
			{
				if(!isset($result[$type][$notification[0]]))
				{
					$result[$type][$notification[0]] = 0;
				}
				$result[$type][$notification[0]]++;
			}
			//echo them out
			foreach($result[$type] as $notificationName => $count)
			{
				$html .= sprintf(
							$nhtml
							,$type
							,($count > 1) 
								? sprintf('(%d) %s', $count, SLocalization::get($notificationName))
								: sprintf('%s', SLocalization::get($notificationName))
					);
			}
		}
		return $html;
	}
	
	//called by template {NotificationCenter:notifier}
	function notifier()
	{
		$notifications = $this->notifyUser();
		if(!empty($notifications))
		{
			return '<div id="notifier"><div id="notifications">'.$notifications.'</div></div>';
		}
		else
		{
			return '';
		}
	}
	
	function allowCallFromTemplate($function)
	{
		return $function == 'notifier';
	}
}

?>
