<?php
/**
 * Description of Content
 *
 * @author lse
 */
class View_Content_EventEndDate
	extends
		_View_Content_Base
	implements
		Interface_View_DisplayXHTML,
		Interface_View_Content
{
	protected $dateFormat = null;

	public function toXHTML() {
		$val = '';
		if($this->shouldDisplay() && $this->content->hasComposite('EventDates')){
			$df = ($this->dateFormat == null) ? Core::settings()->get('dateformat') : $this->dateFormat;
			$val = $this->wrapXHTML('EventEndDate', date($df, $this->content->EventEndDate));
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