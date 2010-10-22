<?php
/**
 * Description of ScopeNext
 *
 * @author lse
 */
class View_Content_ScopePager
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
			if($scope instanceof Interface_Content_FiniteScope)
			{
				$max = $scope->getNumberOfAvailablePages();
				$range = array(1,intval($max));//always has a page 1
				$current = $scope->getNumberOfCurrentPage();
				for($i = $current - 2; $i <= $current + 2; $i++){
					if($i > 0 && $i < $max){
						$range[] = $i;
					}
				}
				$range = array_unique($range);
				sort($range);
				$last = 0;
				$out = '';
				foreach($range as $i => $pageNo){
					if($last < $pageNo - 1){
						$out .= '<span>&nbsp;</span>';
					}
					$out .= sprintf(
						'<a href="%s"%s>%s</a>',
						$scope->getLinkToPage($pageNo),
						($pageNo == $current ? ' class="currentPage"': ''),
						$pageNo
					);
					$last = $pageNo;
				}
				$val = $this->wrapXHTML('ScopePager', $out);
			}
		}
		return $val;
	}
}
?>