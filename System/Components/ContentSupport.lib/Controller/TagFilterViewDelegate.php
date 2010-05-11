<?php
/**
 * Description of TagFilterViewDelegate
 *
 * @author lse
 */
class Controller_TagFilterViewDelegate {
	protected $hideIfTagged = array();
	protected $showIfTagged = array();

	public function contentViewShouldDisplay(_View_Content_Base $view, Interface_Content $content){
		if($content === null){
			return false;
		}
		$visible = true;
		$tags = $content->getTags();
        //needs at least one matching tag to be shown
        if(count($this->showIfTagged) > 0)
        {
            $visible = count(array_intersect($tags, $this->showIfTagged)) > 0;
        }
        //one intersection here and it will be hidden
        if($visible && count($this->hideIfTagged) > 0)
        {
            $visible = count(array_intersect($tags, $this->hideIfTagged)) == 0;
        }
        return $visible;
	}

	public function  __sleep() {
		return array('hideIfTagged', 'showIfTagged');
	}

	public function getTagsForHidingContent(){
		return $this->hideIfTagged;
	}

	public function setTagsForHidingContent(array $value){
		$this->hideIfTagged = array();
		foreach ($value as $v){
			$this->hideIfTagged[] = strval($v);
		}
	}

	public function getTagsForShowingContent(){
		$this->showIfTagged;
	}

	public function setTagsForShowingContent(array $value){
		$this->showIfTagged = array();
		foreach ($value as $v){
			$this->showIfTagged[] = strval($v);
		}
	}
}
?>