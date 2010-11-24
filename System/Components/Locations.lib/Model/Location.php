<?php
class Model_Location
{
	protected $id, $name = '', $address, $longitude, $latitude, $accuracy = 0 /* = map zoom */;
	protected $changed = false;

	/**
	 * create location model
	 * @param string $name
	 */
	public function  __construct($name = null) {
		$this->name = $name;
		if(!empty ($name)){
			$this->initialize();
		}
	}

	/**
	 * destructor - calls store()
	 */
	public function  __destruct() {
		$this->store();
	}

	/**
	 * assert properly initialized model
	 */
	protected function assertState(){
		if(empty ($this->name)){
			throw new Exception('model not initialized');
		}
	}

	/**
	 * check if name is valid
	 * @param string $name
	 */
	protected function validateName($name){
		if(empty ($name)){
			throw new XArgumentException('name empty');
		}
		if(self::exists($name))
		{
			throw new Exception('name already exists');
		}
	}

	public static function exists($name){
		return 1 == Core::Database()
			->createQueryForClass('Model_Location')
			->call('locationExists')
			->withParameters($name)
			->fetchSingleValue();
	}

	/**
	 * load data from db
	 */
	protected function initialize(){
		$res = Core::Database()
			->createQueryForClass($this)
			->call('load')
			->withParameters($this->id, $this->name);
		$row = $res->fetchResult();
		$res->free();
		if($row){
			list(
				$this->id,
				$this->name,
				$this->address,
				$this->latitude,
				$this->longitude,
				$this->accuracy
			) = $row;
		}
		else{
			throw new Exception('location not found');
		}
		return $this;
	}

	/**
	 * insert new location in db
	 * @param string $name
	 */
	public function initializeAs($name){
		if($this->name !== null){
			throw new Exception('model already initialized');
		}
		$this->validateName($name);
		$this->id = Core::Database()
			->createQueryForClass($this)
			->call('create')
			->withParameters($name)
			->executeInsert();
		return $this->initialize();
	}

	/**
	 * update db entry if necessary
	 */
	public function store(){
		if($this->changed){
			Core::Database()
				->createQueryForClass($this)
				->call('update')
				->withParameters($this->name, $this->address, $this->latitude, $this->longitude,  $this->accuracy, $this->id)
				->execute();
		}
	}

	/**
	 * get all aliases from contents located here
	 * @return array
	 */
	public function getAliases(){
		$this->assertState();
		//TODO fetch list of aliases
	}

	/**
	 * @return string
	 */
	public function getAddress(){
		$this->assertState();
		return $this->address;
	}

	/**
	 * @param string $newAddress
	 */
	public function setAddress($newAddress){
		$this->assertState();
		$this->address = $newAddress;
		$this->changed = true;
	}

	/**
	 * @return Model_GeoCoordinates
	 */
	public function getCoordinates(){
		$this->assertState();
		if($this->latitude !== null && $this->longitude !== null){
			return new Model_GeoCoordinates($this->latitude, $this->longitude);
		}
		else{
			return null;
		}
	}

	public function hasCoordinates(){
		return $this->latitude != null
			&& $this->longitude != null;
	}

	/**
	 * @param Model_GeoCoordinates $coordinates
	 */
	public function setCoordinates(Model_GeoCoordinates $coordinates){
		$this->assertState();
		$dec = $coordinates->getDecimal();
		$this->latitude = $dec[Model_GeoCoordinates::LAT];
		$this->longitude = $dec[Model_GeoCoordinates::LONG];
		$this->changed = true;
	}

	/**
	 * remove coordinates assigned to this address
	 */
	public function removeCoordinates(){
		$this->assertState();
		$this->latitude = null;
		$this->longitude = null;
		$this->changed = true;
	}

	/**
	 * @return string
	 */
	public function getName(){
		$this->assertState();
		return $this->name;
	}

	/**
	 * rename location
	 * @param string $newName
	 */
	public function setName($newName){
		$this->assertState();
		$this->validateName($newName);
		$this->name = $newName;
		$this->changed = true;
	}

	/**
	 * @return string
	 */
	public function getAccuracy(){
		$this->assertState();
		return $this->accuracy;
	}

	/**
	 * rename location
	 * @param string $newName
	 */
	public function setAccuracy($newAccuracy){
		$this->assertState();
		if(!is_int($newAccuracy)){
			throw new XArgumentException('accuracy not an int value');
		}
		$this->accuracy = $newAccuracy;
		$this->changed = true;
	}

}
?>