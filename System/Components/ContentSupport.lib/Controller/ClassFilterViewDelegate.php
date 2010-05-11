<?php
/**
 * Description of Controller_ClassFilterViewDelegate
 *
 * @author lse
 */
class Controller_ClassFilterViewDelegate {
	protected $hideClasses = array();
	protected $showClasses = array();

	public function contentViewShouldDisplay(_View_Content_Base $view, Interface_Content $content){
		if($content === null){
			return false;
		}
		$visible = true;
		$class = get_class($content);
        //needs at least one matching tag to be shown
        if(count($this->showClasses) > 0)
        {
            $visible = in_array($class, $this->showClasses);
        }
        //one intersection here and it will be hidden
        if($visible && count($this->hideClasses) > 0)
        {
            $visible = !in_array($class, $this->hideClasses);
        }
        return $visible;
	}

	public function  __sleep() {
		return array('hideClasses', 'showClasses');
	}

	public function getClassesHidingContent(){
		return $this->hideClasses;
	}

	public function setClassesHidingContent(array $value){
		$this->hideClasses = array();
		foreach ($value as $v){
			$this->hideClasses[] = strval($v);
		}
	}

	public function getClassesShowingContent(){
		$this->showClasses;
	}

	public function setClassesShowingContent(array $value){
		$this->showClasses = array();
		foreach ($value as $v){
			$this->showClasses[] = strval($v);
		}
	}
}
?>