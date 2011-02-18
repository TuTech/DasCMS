<?php
/**
 * Description of Content
 *
 * @author lse
 */
class View_Content_Icon
	extends
		_View_Content_Base
	implements
		Interface_View_DisplayXHTML,
		Interface_View_Content
{
	protected $iconSize;

	public function toXHTML() {
		$val = '';
		$size = $this->iconSize;
		if($size == null){
			$size = View_UIElement_Icon::SMALL;
		}
		if($this->shouldDisplay()){
			$val = $this->wrapXHTML('Icon', $this->content->getIcon()->asSize($size));
		}
		return $val;
	}

	protected function getPersistentAttributes() {
		return array('iconSize');
	}

	public function getIconSize(){
		return $this->iconSize;
	}

	public function setIconSize($value){
		if(View_UIElement_Icon::isSize($value)){
			$this->iconSize = $value;
		}
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