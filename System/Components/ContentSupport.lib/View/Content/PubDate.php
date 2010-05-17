<?php
/**
 * Description of View_Content_PubDate
 *
 * @author lse
 */
class View_Content_PubDate
	extends
		_View_Content_Base
	implements
		Interface_View_DisplayXHTML,
		Interface_View_Content
{
	protected $dateFormat = null;

	public function toXHTML() {
		$val = '';
		if($this->shouldDisplay()){
			$df = ($this->dateFormat == null) ? Core::settings()->get('dateformat') : $this->dateFormat;
			$val = $this->wrapXHTML('PubDate', date($df, $this->content->getPubDate()));
		}
		return $val;
	}

	protected function getPersistentAttributes() {
		return array('dateFormat');
	}

	public function getDateFormat(){
		return $this->dateFormat;
	}

	public function setDateFormat($value){
		$this->dateFormat = $value;
	}
}
?>