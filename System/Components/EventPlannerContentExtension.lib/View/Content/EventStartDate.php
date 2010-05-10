<?php
/**
 * Description of Content
 *
 * @author lse
 */
class View_Content_EventStartDate
	extends
		_View_Content_Base
	implements
		Interface_View_DisplayXHTML,
		Interface_View_Content
{
	public function toXHTML() {
		$val = '';
		if($this->shouldDisplay() && isset($this->content->EventStartDate)){
			$val = $this->wrapXHTML('EventStartDate', isset($this->content->EventStartDate));
		}
		return $val;
	}
}
?>