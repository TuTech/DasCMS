<?php
/**
 * Description of ScopePageNumber
 *
 * @author lse
 */
class View_Content_ScopePageNumber
	extends
		_View_Content_Base
	implements
		Interface_View_DisplayXHTML,
		Interface_View_Content
{
	public function toXHTML() {
		$val = '';
		if($this->shouldDisplay()
				&& $this->content instanceof Interface_Content_HasScope)
		{
			$scope = $this->content->getScope();
			if($scope instanceof Interface_Content_FiniteScope){
				$val = $this->wrapXHTML('ScopePageNumber', $scope->getNumberOfCurrentPage());
			}
		}
		return $val;
	}
}
?>