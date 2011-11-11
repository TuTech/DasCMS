<?php
/**
 * Description of Content
 *
 * @author lse
 */
class View_Content_Description
	extends
		_View_Content_Base
	implements
		Interface_View_DisplayXHTML,
		Interface_View_Content
{
	public function toXHTML() {
		$val = '';
		if($this->shouldDisplay()){
			$val = $this->wrapXHTML('Description', $this->content->getDescription());
		}
		return $val;
	}
}
?>