<?php
/**
 * Description of ScopeNext
 *
 * @author lse
 */
class View_Content_ScopeNext
	extends
		_View_Content_Base
	implements
		Interface_View_DisplayXHTML,
		Interface_View_Content
{
	public function toXHTML() {
		$val = '';
		if($this->shouldDisplay() && $this->content instanceof Interface_Content_HasScope){
			$scope = $this->content->getScope();
			$isFinite = $scope instanceof Interface_Content_FiniteScope;
			if(($isFinite && !$scope->isLastPage())
					|| (!$isFinite && $scope->getNextPageLink() != null))
			{
				$text = $this->getLinkCaption();
				if(empty ($text)){
					$text = $scope->getNextPageTitle();
				}
				$link = $scope->getNextPageLink();
				$frame = $this->getLinkTargetFrame();

				$val = sprintf(
						'<a href="%s"%s>%s</a>',
						$link,
						(empty ($frame)) ? '' : sprintf(' target="%s"', String::htmlEncode($frame)),
						String::htmlEncode($text)
				);

				$val = $this->wrapXHTML('ScopeNext', $val);
			}
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
}
?>