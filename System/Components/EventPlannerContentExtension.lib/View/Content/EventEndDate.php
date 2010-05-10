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
	public function toXHTML() {
		$val = '';
		if($this->shouldDisplay() && isset($this->content->EventEndDate)){
			$val = $this->wrapXHTML('EventEndDate', isset($this->content->EventEndDate));
		}
		return $val;
	}
}
?>