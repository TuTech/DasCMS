<?php
/**
 * Description of ModifyDate
 *
 * @author lse
 */
class View_Content_ModifyDate
	extends
		_View_Content_Base
	implements
		Interface_View_DisplayXHTML,
		Interface_View_Content
{
	protected $dateFormat = null;

	public function toXHTML() {
		$val = '';
		if($this->shouldDisplay() && $this->content->hasComposite('History')){
			$df = ($this->dateFormat == null) ? LConfiguration::get('dateformat') : $this->dateFormat;
			$val = $this->wrapXHTML('ModifyDate', date($df, $this->content->ModifyDate));
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