<?php
/**
 * Description of ViewSensorDelegate
 *
 * @author lse
 */
class Controller_View_ContentActivityDetectorDelegate {
    protected $detectInViews = array();

	public function contentViewShouldDisplay(_View_Content_Base $view, Interface_Content $content){
		$views = $this->detectInViews;
		$activeViews = VSpore::activeSpores();
		$contentActiveIn = array();
		$id = $content->getId();
		
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

	public function  __sleep() {
		return array('detectInViews');
	}

	public function getViewsToDetectIn(){
		return $this->detectInViews;
	}

	public function setViewsToDetectIn(array $value){
		$this->detectInViews = array();
		foreach ($value as $v){
			$this->detectInViews[] = strval($v);
		}
	}
}
?>