<?php
/**
 * Description of Content
 *
 * @author lse
 */
class View_Content_Subtitle
	extends
		_View_Content_Base
	implements
		Interface_View_DisplayXHTML,
		Interface_View_Content
{
	public function toXHTML() {
		$val = '';
		if($this->shouldDisplay()){
			$val = $this->wrapXHTML('Subtitle', $this->content->getSubTitle());
		}
		return $val;
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

	protected function getWrapperTag() {
		return 'h3';
	}
}
?>