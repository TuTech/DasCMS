<?php
class Controller_ContentPublication
	implements 
		Interface_Singleton,
		Event_Handler_ContentChanged
{
	private static $instance;

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
		$db = Core::Database();
		$dba = $db->createQueryForClass($this);
		$q = $dba->call('getChanged')->withoutParameters();
		$NOT_PUBLIC = 0;
		$PUBLIC = 1;

		$db->beginTransaction();

		//fetch changes
		$toUpdate = array($NOT_PUBLIC => array(), $PUBLIC => array());
		$updated = array($NOT_PUBLIC => array(), $PUBLIC => array());
		while($row = $q->fetchResult()){
			$toUpdate[$row[0]] = $row[1];
		}
		$q->free();
		
		//publish & revoke
		$call = $dba->call('changeStatus');
		$changed = 0;
		foreach(array($NOT_PUBLIC, $PUBLIC) as $status){
			foreach ($toUpdate[$status] as $contentToChange){
				if($call->withParameters(!$status, $contentToChange)->execute()){
					$updated[!$status] = $contentToChange;
					$changed++;
				}
			}
		}
		
		$db->commitTransaction();

		//send events
		$CC = Controller_Content::getInstance();
		foreach ($updated[$PUBLIC] as $changedContentAlias){
			$e = new Event_ContentPublished($this, $CC->openContent($changedContentAlias));
		}
		foreach ($updated[$NOT_PUBLIC] as $changedContentAlias){
			$e = new Event_ContentRevoked($this, $CC->openContent($changedContentAlias));
		}

		return $changed;
	}
}
?>
