<?php
class Import_Version1_JSONReferences implements Import_Version1_References
{
	protected $data;
	public function __construct(array $referenceData) {
		$this->data = $referenceData;
	}

	/**
	 * @return array
	 */
	public function getReferenceSections() {
		return array_values($this->data);
	}

	/**
	 * @param string $section
	 * @return int
	 */
	public function getReferenceCountInSection($section) {
		if(isset ($this->data[$section])){
			return count($this->data[$section]);
		}
		return 0;
	}

	/**
	 * @param int $number
	 * @param string $section
	 * @return Import_Version1_Reference
	 */
	public function getReferenceInSection($number, $section) {
		if(isset ($this->data[$section])
				&& isset ($this->data[$section][$number])
				&& isset ($this->data[$section][$number]['rel'])
				&& isset ($this->data[$section][$number]['href'])
		){
			return new Import_Version1_JSONReference(
					$this->data[$section][$number]['rel'],
					$this->data[$section][$number]['href']
				);
		}
		return null;
	}
}
?>
