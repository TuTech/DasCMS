<?php
/**
 * Description of Content
 *
 * @author lse
 */
class View_Content_CustomCode
	extends
		_View_Content_Base
	implements
		Interface_View_DisplayXHTML,
		Interface_View_Content
{
	protected $code = '';

	public function toXHTML() {
		return $code;
	}

	public function getCode() {
		return $this->code;
	}

	public function setCode($value) {
		$this->code = $value;
	}

	protected function getPersistentAttributes() {
		return array('code');
	}
}
?>