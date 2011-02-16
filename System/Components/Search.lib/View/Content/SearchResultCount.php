<?php
/**
 * @author lse
 */
class View_Content_SearchResultCount
	extends
		_View_Content_Base
	implements
		Interface_View_DisplayXHTML,
		Interface_View_Content
{
	public function toXHTML() {
		$val = '';
		$c = $this->content;
		if($this->shouldDisplay() && $c instanceof CSearch)
		{
			$val = $this->wrapXHTML('ResultCount',  $c->getResultCount());
		}
		return $val;
	}
}
?>