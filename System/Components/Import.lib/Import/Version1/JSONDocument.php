<?php
class Import_Version1_JSONDocument implements Import_Version1_Document
{
	protected $data;
	public function  __construct(array $jsonData) {
		$this->data = $jsonData;
		if($this->data == null || !is_array($this->data)){
			throw new Exception('could not load data');
		}
		if(!isset ($this->data['importId'])
				|| !isset ($this->data['type'])
				|| !isset($this->data['title']))
		{
			throw new Exception('insufficient data');
		}
	}

	protected function getDataForKey($key, $defaultValue = '', $subKey = null){
		if($subKey == null && isset ($this->data[$key])){
			return $this->data[$key];
		}
		elseif(isset ($this->data[$key]) && isset($this->data[$key][$subKey])){
			return $this->data[$key][$subKey];
		}
		else return $defaultValue;
	}

	protected function convertToDate($dateData){
		if(is_int($dateData)){
			return $dateData;
		}
		return strtotime($dateData);
	}


	public function getImportId() {
		return $this->data['importId'];
	}

	public function getType() {
		return $this->data['type'];
	}

	public function getTitle() {
		return $this->data['title'];
	}

	public function getTags() {
		 $tags = $this->getDataForKey('tags', array());
		 if(!is_array($tags)){
			 $tags = Controller_Tags::parseString($tags);
		 }
		 return $tags;
	}

	public function getSubTitle() {
		return $this->getDataForKey('subTitle');
	}

	public function getDescription() {
		return $this->getDataForKey('description');
	}

	public function getPubDate() {
		return $this->convertToDate($this->getDataForKey('pubDate'));
	}

	public function getRevokeDate() {
		return $this->convertToDate($this->getDataForKey('revokeDate'));
	}

	public function getCreateDate() {
		return $this->convertToDate($this->getDataForKey('created', 0, 'date'));
	}
	
	public function getCreator() {
		return $this->getDataForKey('created', 0, 'person');
	}
	
	public function getModifyDate() {
		return $this->convertToDate($this->getDataForKey('modified', 0, 'date'));
	}
	
	public function getModifier() {
		return $this->getDataForKey('modified', 0, 'person');
	}

	public function getContentEncoding() {
		return $this->getDataForKey('contentEncoding', 'none');
	}

	public function getContent() {
		return $this->getDataForKey('content');
	}

	/**
	 * @return Import_Version1_Location
	 */
	public function getLocation() {
		$data = $this->getDataForKey('location', array());
		if(!is_array($data)){
			$data = array();
		}
		return new Import_Version1_JSONLocation($data);
	}

	/**
	 * @return Import_Version1_References
	 */
	public function getReferences() {
		$data = $this->getDataForKey('references', array());
		if(!is_array($data)){
			$data = array();
		}
		return new Import_Version1_JSONReferences($data);
	}
}
?>
