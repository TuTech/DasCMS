<?php
/**
 * Description of View_Content_AssignedRelations
 *
 * @author lse
 */
class View_Content_AssignedRelations
	extends
		_View_Content_Base
	implements
		Interface_View_DisplayXHTML,
		Interface_View_Content
{
	public function toXHTML() {
		$val = '';
		if($this->shouldDisplay() && $this->content->hasComposite('AssignedRelations')){
			$val = $this->wrapXHTML('AssignedRelations', $this->content->getAssignedRelations());
		}
		return $val;
	}
}
?>