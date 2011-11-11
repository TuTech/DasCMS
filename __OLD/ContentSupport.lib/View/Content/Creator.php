<?php
/**
 * Description of Content
 *
 * @author lse
 */
class View_Content_Creator
	extends
		_View_Content_Base
	implements
		Interface_View_DisplayXHTML,
		Interface_View_Content
{
	public function toXHTML() {
		$val = '';
		if($this->shouldDisplay() && $this->content->hasComposite('History')){
			$val = $this->wrapXHTML('Creator', $this->content->getCreatedBy());
		}
		return $val;
	}
}
?>