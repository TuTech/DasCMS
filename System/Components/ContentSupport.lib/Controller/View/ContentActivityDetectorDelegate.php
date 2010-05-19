<?php
/**
 * Description of ViewSensorDelegate
 *
 * @author lse
 */
class Controller_View_ContentActivityDetectorDelegate {
    protected $detectInViews = array();
    protected $showIfViewsContainContent = array();
    protected $hideIfViewsContainContent = array();

	private function isActiveIn(array &$views, &$activeViews, $id){
		$show = false;
		foreach ($views as $vcc){
			if(array_key_exists($vcc, $activeViews)){
				$v = VSpore::byName($vcc);
				$show = $show || ($id == $v->getContent()->getId());
			}
		}
		return $show;
	}

	public function contentViewShouldDisplay(_View_Content_Base $view, Interface_Content $content){
		$show = true;
		$activeViews = VSpore::activeSpores();
		$id = $content->getId();

		if(count($this->showIfViewsContainContent) > 0){
			$show = $this->isActiveIn($this->showIfViewsContainContent, $activeViews, $id);
		}

		if($show && count($this->hideIfViewsContainContent) > 0){
			$show = !$this->isActiveIn($this->hideIfViewsContainContent, $activeViews, $id);
		}

		if($show){
			$views = $this->detectInViews;
			$contentActiveIn = array();

			if(count($views) == 0){
				//default to all views
				$views = $activeViews;
			}
			else{
				//only active views
				$views = array_intersect($views, $activeViews);
			}

			//find views our content is active in
			foreach ($views as $viewName){
				$v = VSpore::byName($viewName);
				if($id == $v->getContent()->getId()){
					$contentActiveIn[] = 'active_in_view_'.$viewName;
				}
			}

			//set css class
			$contentActiveIn[] = $view->getCustomCSSClass();
			$view->setCustomCSSClass(implode(' ', $contentActiveIn));
		}
		return $show;
	}

	public function  __sleep() {
		return array('detectInViews', 'showIfViewsContainContent', 'hideIfViewsContainContent');
	}

	public function getShowIfViewsContainContent(){
		return $this->showIfViewsContainContent;
	}

	public function setShowIfViewsContainContent(array $views){
		$this->showIfViewsContainContent = array_values($views);
	}

	public function getHideIfViewsContainContent(){
		return $this->hideIfViewsContainContent;
	}

	public function setHideIfViewsContainContent(array $views){
		$this->hideIfViewsContainContent = array_values($views);
	}

	public function getViewsToDetectIn(){
		return $this->detectInViews;
	}

	public function setViewsToDetectIn(array $value){
		$this->detectInViews = array_values($value);
	}
}
?>