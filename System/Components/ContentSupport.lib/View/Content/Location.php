<?php
/**
 * Description of Content
 *
 * @author lse
 */
class View_Content_Location
	extends
		_View_Content_Base
	implements
		Interface_View_DisplayXHTML,
		Interface_View_Content
{
	//TODO address formatting
	public function toXHTML() {
		$val = '';
		if($this->shouldDisplay()){
			$val = $this->wrapXHTML('Location', $this->content->getDescription());
		}
		return $val;
	}
}
?>