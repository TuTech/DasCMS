<?php
class Import_Version1_JSONLocation implements Import_Version1_Location
{
	protected $data;
	public function __construct($data) {
		$this->data = $data;
	}

	public function getLocationName() {
		return isset($this->data['name']) ? $this->data['name'] : null;
	}

	public function getAddress() {
		return isset($this->data['address']) ? $this->data['address'] : null;
	}

	public function getLatitude() {
		return isset($this->data['latitude']) ? $this->data['latitude'] : null;
	}

	public function getLongitude() {
		return isset($this->data['longitude']) ? $this->data['longitude'] : null;
	}

	public function hasData(){
		return !empty ($this->data['name'])
				|| !empty ($this->data['address'])
				|| isset ($this->data['latitude'])
				|| isset ($this->data['longitude']);
	}
}
?>
