<?php
/**
 * Description of Content
 *
 * @author lse
 */
class View_Content_PreviewImage
	extends
		_View_Content_Base
	implements
		Interface_View_DisplayXHTML,
		Interface_View_Content
{
	protected $imageWidth = 100,
			  $imageHeight = 100,
			  $imageFillColor = '#ffffff',
			  $scaleMethod = WImage::MODE_SCALE_TO_MAX,
			  $scaleEnforcementMethod = Wimage::FORCE_BY_FILL;

	public function toXHTML() {
		$val = '';
		if($this->shouldDisplay()){
			$img = $this->content->getPreviewImage();
			if($this->imageHeight != null && $this->imageWidth != null){
				$img = $img->scaled($this->imageWidth, $this->imageHeight, $this->scaleMethod, $this->scaleEnforcementMethod, $this->imageFillColor);
			}
			$val = $this->wrapXHTML('PreviewImage', strval($img));
		}
		return $val;
	}

	protected function getPersistentAttributes() {
		return array(
			'imageWidth',
			'imageHeight',
			'imageFillColor',
			'scaleMethod',
			'scaleEnforcementMethod'
		);
	}

	public function getImageFillColor(){
		return $this->imageWidth;
	}

	public function setImageFillColor($value){
		if($value != null && !preg_match('/^#[a-fA-F0-9]{6}$/', $value)){
			return;
		}
		$this->imageFillColor = $value;
	}

	public function getImageWidth(){
		return $this->imageWidth;
	}

	public function setImageWidth($value){
		if(!is_numeric($value) || ($value < 1) || ($value >4096)){
			return;
		}
		$this->imageWidth = $value;
	}

	public function getImageHeight(){
		return $this->imageHeight;
	}

	public function setImageHeight($value){
		if(!is_numeric($value) || ($value < 1) || ($value >4096)){
			return;
		}
		$this->imageHeight = $value;
	}

	public function getScaleMethod(){
		return $this->scaleMethod;
	}

	public function setScaleMethod($value){
		if($value != WImage::MODE_FORCE && $value != WImage::MODE_SCALE_TO_MAX){
			return ;
		}
		$this->scaleMethod = $value;
	}

	public function getScaleEnforcementMethod(){
		return $this->scaleEnforcementMethod;
	}

	public function setScaleEnforcementMethod($value){
		if($value != WImage::FORCE_BY_CROP
				&& $value != WImage::FORCE_BY_FILL
				&& $value != WImage::FORCE_BY_STRETCH){
			return ;
		}
		$this->scaleEnforcementMethod = $value;
	}

	public function getLinkTargetFrame() {
		return parent::getLinkTargetFrame();
	}

	public function setLinkTargetFrame($value) {
		parent::setLinkTargetFrame($value);
	}

	public function getLinkTargetView() {
		return parent::getLinkTargetView();
	}

	public function setLinkTargetView($value) {
		parent::setLinkTargetView($value);
	}
}
?>