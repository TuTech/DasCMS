<?php
class Controller_SearchCache
	implements
		Event_Handler_ContentChanged,
		Event_Handler_ContentCreated,
		Event_Handler_ContentDeleted,
		Event_Handler_ContentPublished,
		Event_Handler_ContentRevoked,
		Event_Handler_PermissionsChanged,
		Interface_Singleton
{
	private static $instance;
	private function  __construct() {}
	private function  __clone() {}

	public static function getInstance() {
		if(!self::$instance){
			self::$instance = new Controller_SearchCache();
		}
		return self::$instance;
	}

	public function handleEventContentChanged(Event_ContentChanged $e) {
		$this->flushCache();
	}

	public function handleEventContentCreated(Event_ContentCreated $e) {
		$this->flushCache();
	}

	public function handleEventContentDeleted(Event_ContentDeleted $e) {
		$this->flushCache();
	}

	public function handleEventContentPublished(Event_ContentPublished $e) {
		$this->flushCache();
	}

	public function handleEventContentRevoked(Event_ContentRevoked $e) {
		$this->flushCache();
	}

	public function handleEventPermissionsChanged(Event_PermissionsChanged $e) {
		$this->flushCache();
	}

	protected function flushCache(){
		Search_Engine::getInstance()->flush();
	}
}
?>
