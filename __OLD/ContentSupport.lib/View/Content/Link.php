<?php
/**
 * Description of Content
 *
 * @author lse
 */
class View_Content_Link
	extends
		_View_Content_Base
	implements
		Interface_View_DisplayXHTML,
		Interface_View_Content
{
	public function toXHTML() {
		$val = '';
		if($this->shouldDisplay()){
			$val = $this->wrapXHTML('Link', $this->getLinkCaption());
		}
		return $val;
	}

	public function getLinkCaption() {
		return parent::getLinkCaption();
	}

	public function setLinkCaption($value) {
		parent::setLinkCaption($value);
	}

	public function getLinkTargetFrame() {
		return parent::getLinkTargetFrame();
	}

	public function setLinkTargetFrame($value) {
		parent::setLinkTargetFrame($value);
	}

	public function getLinkTargetView() {
		return parent::getLinkTargetView();
	}

	public function setLinkTargetView($value) {
		parent::setLinkTargetView($value);
	}
}
?>