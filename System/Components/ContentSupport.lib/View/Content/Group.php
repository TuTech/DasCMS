<?php
/**
 * Description of Group
 *
 * @author lse
 */
class View_Content_Group
	extends
		_View_Content_Base
	implements
		Interface_View_DisplayXHTML,
		Interface_View_Content
{
	protected $subViews;

	public function acceptContent(Interface_Content $content) {
		parent::acceptContent($content);
		if(is_array($this->subViews)){
			foreach ($this->subViews as $view){
				$view->acceptContent($content);
			}
		}
	}

	public function toXHTML() {
		$val = '';
		if($this->shouldDisplay() && is_array($this->subViews)){
			$cssTpl = "Group_item Group_item_%d";
			for($i = 0; $i < count($this->subViews); $i++){
				$this->subViews[$i]->addCustomCSSClass(sprintf($cssTpl, $i+1));
				$val .=  $this->subViews[$i]->toXHTML();
			}
			$val = $this->wrapXHTML('Group', $val);
		}
		return $val;
	}

	public function addSubView(_View_Content_Base $subView){
		if($this->subViews == null){
			$this->subViews = array();
		}
		$this->subViews[] = $subView;
	}

	protected function getPersistentAttributes() {
		return array('subViews');
	}
}
?>