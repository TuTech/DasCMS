<?php
class SysLog implements Interface_Logger
{
	private static $instance;

	/**
	 * @return SysLog
	 */
	public static function getLogger() {
		if(!self::$instance){
			self::$instance = new SysLog();
		}
		return self::$instance;
	}

	/**
	 * @param string $message
	 * @param int $priority LOG_* constants
	 * @return bool
	 */
	public function log($message, $priority = LOG_NOTICE) {
		openlog('DasCMS', LOG_CONS | LOG_PID, LOG_USER);
		$res = syslog($priority, '['.CMS_ROOT.'] '.$message);
		closelog();
		return $res;
	}
}
?>
