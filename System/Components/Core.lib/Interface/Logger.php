<?php
interface Interface_Logger
{
	/**
	 * @return Interface_Logger
	 */
	public static function getLogger();

	public function log($message, $priority = LOG_NOTICE);
}
?>
