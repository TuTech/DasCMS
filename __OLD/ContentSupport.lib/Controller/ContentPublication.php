<?php
class Controller_ContentPublication
	implements 
		Interface_Singleton,
		Event_Handler_ContentChanged
{
	const IS_PUBLIC = 1;
	const IS_PRIVATE = 0;
	
	private static $instance;

	/**
	 * @return Controller_ContentPublication
	 */
	public static function getInstance() {
		 if(!self::$instance){
			self::$instance = new Controller_ContentPublication();
		 }
		 return self::$instance;
	}

	public function handleEventContentChanged(Event_ContentChanged $e) {
		$this->updatePublications();
	}

	public function updatePublications(){
		
		//init
		$db = Core::Database()->createQueryForClass($this);
		$db->beginTransaction();

		$q = $db->call('getChanged')->withoutParameters();

		//fetch changes
		$toUpdate = array(self::IS_PRIVATE => array(), self::IS_PUBLIC => array());
		$updated = array(self::IS_PRIVATE => array(), self::IS_PUBLIC => array());
		while($row = $q->fetchResult()){
			$toUpdate[$row[0]][] = $row[1];
		}
		$q->free();
		
		//publish & revoke
		$call = $db->call('changeStatus');
		$changed = 0;
		foreach(array(self::IS_PRIVATE, self::IS_PUBLIC) as $status){
			foreach ($toUpdate[$status] as $contentToChange){
				if($call->withParameters(!$status, $contentToChange)->execute()){
					$updated[!$status][] = $contentToChange;
					$changed++;
				}
			}
		}
		
		$db->commitTransaction();

		//send events
		$CC = Controller_Content::getInstance();
		foreach ($updated[self::IS_PUBLIC] as $changedContentAlias){
			$e = new Event_ContentPublished($this, $CC->openContent($changedContentAlias));
		}
		foreach ($updated[self::IS_PRIVATE] as $changedContentAlias){
			$e = new Event_ContentRevoked($this, $CC->openContent($changedContentAlias));
		}

		return $changed;
	}
}
?>
