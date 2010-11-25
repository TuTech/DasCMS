<?php
class Controller_Locations
{
	private static $instance;
	private function  __construct() {}
	private function  __clone() {}

	/**
	 * @return Controller_Locations
	 */
	public static function getInstance(){
		if(self::$instance == null){
			self::$instance = new Controller_Locations();
		}
		return self::$instance;
	}

	/**
	 * @param string $name
	 * @return Controller_Location
	 */
	public function createLocation($name){
		$loc = new Model_Location();
		return new Controller_Location($loc->initializeAs($name));
	}
	
	public function deleteLocation($name){
		//TODO call delete
	}

	public function renameLocation($oldName, $newName){
		$loc = new Model_Location($oldName);
		$loc->setName($newName);
		$loc->store();
	}

	/**
	 * @param string $name
	 * @return Controller_Location
	 */
	public function getLocation($name){
		$loc = null;
		try{
			if(Model_Location::exists($name)){
				$loc = new Controller_Location(new Model_Location($name));
			}
		}
		catch (Exception $e){
			$loc = null;
		}
		return $loc;
	}

	public function getLocationForAlias($alias){
		//TODO resolve
	}
	public function setLocationForAlias($alias){
		//TODO assign
	}
}
?>
