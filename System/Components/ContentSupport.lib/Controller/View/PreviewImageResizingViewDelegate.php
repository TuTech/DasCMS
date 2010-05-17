<?php
/**
 * Description of PreviewImageResizingViewDelegate
 *
 * @author lse
 */
class Controller_View_PreviewImageResizingViewDelegate {
    //tag => array(w:,h:,m:,f:)
	protected $resizeMap = array();

	public function contentViewShouldDisplay(_View_Content_Base $view, Interface_Content $content){
		if($content !== null && $view instanceof View_Content_PreviewImage){
			$tags = $content->getTags();
			$resize = null;
			foreach ($tags as $tag){
				if(array_key_exists($tag, $this->resizeMap)){
					$resize = $this->resizeMap[$tag];
				}
			}
			if($resize){
				$view->getImageWidth($resize['w']);
				$view->getImageHeight($resize['h']);
				$view->setScaleMethod($resize['m']);
				$view->setScaleEnforcementMethod($resize['f']);
				$view->setImageFillColor($resize['c']);
			}
		}
		return true;
	}

	public function setResizeForTag($tag, $width, $height, $mode, $enforcement, $color = null){
		$this->resizeMap[$tag] = array('w' => $width, 'h' => $height, 'm' => $mode, 'f' => $enforcement, 'c' => $color);
	}

	public function removeResizeTag($tag){
		unset ($this->resizeMap[$tag]);
	}

	public function  __sleep() {
		return array('resizeMap');
	}
}
?>