<?php
class Import_Version1_JSONReference implements Import_Version1_Reference
{
	protected $rel, $href;
	public function __construct($rel, $href) {
		$this->rel = $rel;
		$this->href = $href;
	}

	public function getReferenceType() {
		return $this->rel;
	}

	public function getReferenceValue() {
		return $this->href;
	}
}
?>
