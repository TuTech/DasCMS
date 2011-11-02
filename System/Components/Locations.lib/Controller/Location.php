<?php
class Controller_Location
{
	/**
	 * @var Model_Location
	 */
	protected $model;

	/**
	 * constructor
	 * @param Model_Location $model
	 */
	public function  __construct(Model_Location $model) {
		$this->model = $model;
	}

	/**
	 * get address
	 * @return string
	 */
	public function getAddress(){
		return $this->model->getAddress();
	}

	/**
	 * set new address
	 * @param string $newAddress
	 */
	public function setAddress($newAddress){
		$this->model->setAddress($newAddress);
	}

	/**
	 * returns latitude in decimal format
	 * @return decimal
	 */
	public function getLatitude(){
		return $this->getCoord(Model_GeoCoordinates::LAT);
	}

	/**
	 * sets latitude in decimal format
	 * @param decimal $newLatitude
	 */
	public function setLatitude($newLatitude){
		$this->updateLocation($newLatitude, Model_GeoCoordinates::LAT, true);
	}

	/**
	 * returns latitude in DMS format
	 * @return string
	 */
	public function getDMSLatitude(){
		return $this->getCoord(Model_GeoCoordinates::LAT, false);
	}

	/**
	 * sets latitude in DMS format
	 * @param string $newLatitude
	 */
	public function setDMSLatitude($newLatitude){
		$this->updateLocation($newLatitude, Model_GeoCoordinates::LAT, false);
	}

	/**
	 * returns longitude in decimal format
	 * @return decimal
	 */
	public function getLongitude(){
		return $this->getCoord(Model_GeoCoordinates::LONG);
	}

	/**
	 * sets latitude in decimal format
	 * @param decimal $newLatitude
	 */
	public function setLongitude($newLatitude){
		$this->updateLocation($newLatitude, Model_GeoCoordinates::LONG, true);
	}

	/**
	 * returns longitud in DMS format
	 * @return string
	 */
	public function getDMSLongitude(){
		return $this->getCoord(Model_GeoCoordinates::LONG, false);
	}

	/**
	 * sets longitude in DMS format
	 * @param string $newLatitude
	 */
	public function setDMSLongitude($newLatitude){
		$this->updateLocation($newLatitude, Model_GeoCoordinates::LONG, FALSE);
	}

	/**
	 * returns the name of this location
	 * @return string
	 */
	public function getName(){
		return $this->model->getName();
	}

	/**
	 * get coordinate dtring based on decimals
	 * @return string
	 */
	public function getCoordinates(){
		$coord = $this->model->getCoordinates();
		if($coord){
			$c = $coord->getDecimal();
			return sprintf('%f,%f', $c[Model_GeoCoordinates::LAT], $c[Model_GeoCoordinates::LONG]);
		}
		return '';
	}

	/**
	 * get coordinate dtring based on DMS
	 * @return string
	 */
	public function getDMSCoordinates(){
		$coord = $this->model->getCoordinates();
		if($coord){
			$c = $coord->getDMS();
			return sprintf('%s %s', $c[Model_GeoCoordinates::LAT], $c[Model_GeoCoordinates::LONG]);
		}
		return '';
	}

	/**
	 * are coordinates set
	 * @return bool
	 */
	public function hasCoordinates(){
		return $this->model->hasCoordinates() != null;
	}

	/**
	 * returns zoom level for map embeds
	 * @return int
	 */
	public function getZoom(){
		return $this->model->getAccuracy();
	}

	/**
	 * set new zoom level for map embeds
	 * @param int $newZoom
	 */
	public function setZoom($newZoom){
		$this->model->setAccuracy($newZoom);
	}

	public function store(){
		$this->model->store();
	}

	/**
	 * get a part of a coordinate
	 * @param int $direction
	 * @param bool $asDecimal
	 * @return mixed
	 */
	protected function getCoord($direction, $asDecimal = true){
		$coord = $this->model->getCoordinates();
		if($coord){
			$dec = $asDecimal
				? $this->model->getCoordinates()->getDecimal()
				: $this->model->getCoordinates()->getDMS();
			return $dec[$direction];
		}
		return null;
	}

	/**
	 * update one coordinate part
	 * @param mixed $newValue
	 * @param int $direction
	 * @param bool $asDecimal
	 */
	protected function updateLocation($newValue, $direction, $asDecimal = true){
		$newCoord = null;
		if($newValue !== null && trim($newValue) != ''){
			$direction = ($direction == Model_GeoCoordinates::LAT);
			$opposite = $direction
				? Model_GeoCoordinates::LAT
				: Model_GeoCoordinates::LONG;
			$otherCoord = $this->getCoord($opposite, $asDecimal);
			if($otherCoord === null){
				//because latitude and longitude are set separately we have to give the other one a default value
				$otherCoord = $asDecimal ? 0.0 : '0° 0\' 0"';
			}
			try{
				$newCoord = new Model_GeoCoordinates(
					$direction ? $newValue : $otherCoord,//lat
					$direction ? $otherCoord : $newValue//long
				);
			}
			catch(Exception $e){
				$newCoord = null;
			}
		}
		if($newCoord === null){
			$this->model->removeCoordinates();
		}
		else{
			$this->model->setCoordinates($newCoord);
		}
	}
}
?>