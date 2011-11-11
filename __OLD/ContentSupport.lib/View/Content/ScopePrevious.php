<?php
/**
 * Description of ScopePrevious
 *
 * @author lse
 */
class View_Content_ScopePrevious
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
			if(($isFinite && !$scope->isFirstPage())
					|| (!$isFinite && $scope->getPreviousPageLink() != null))
			{
				$text = $this->getLinkCaption();
				if(empty ($text)){
					$text = $scope->getPreviousPageTitle();
				}
				$link = $scope->getPreviousPageLink();
				$frame = $this->getLinkTargetFrame();

				$val = sprintf(
						'<a href="%s"%s>%s</a>',
						$link,
						(empty ($frame)) ? '' : sprintf(' target="%s"', String::htmlEncode($frame)),
						String::htmlEncode($text)
				);

				$val = $this->wrapXHTML('ScopePrevious', $val);
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