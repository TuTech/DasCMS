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
		if($subkey == null && isset ($this->data[$key])){
			return $this->data[$key];
		}
		elseif(isset ($this->data[$key]) && isset($this->data[$key][$subKey])){
			return $this->data[$key][$subKey];
		}
		else return $defaultValue;
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

	public function getSubTitle() {
		return $this->getDataForKey('subTitle');
	}

	public function getDescription() {
		return $this->getDataForKey('description');
	}

	public function getPubDate() {
		return $this->getDataForKey('pubDate');
	}

	public function getRevokeDate() {
		return $this->getDataForKey('revokeDate');
	}

	public function getCreateDate() {
		return $this->getDataForKey('created', 0, 'date');
	}
	
	public function getCreator() {
		return $this->getDataForKey('created', 0, 'person');
	}
	
	public function getModifyDate() {
		return $this->getDataForKey('modified', 0, 'date');
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
