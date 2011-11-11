<?php
class ContentProxy_ImageController extends Content_ImageController
{
	protected $proxy, $content;

	public function __construct(ContentProxyController $proxy) {
		$this->proxy = $proxy;
		$this->content = $proxy->_content();
	}
	
	/**
	 * the appropriate icon for this content
	 */
	public function icon(){
		$this->content->getIcon();
	}
	
	/**
	 * the image version of the actual content 
	 * (e.g. a pdf2png rendering)
	 */
	public function contentImage(){
		//TODO: Content API => content image
	}
	
	/**
	 * a custom image for this content
	 *  ==> old previewImage
	 */
	public function displayImage(){
		//TODO: Content API => display image
	}
	
	/**
	 * set custom image for this content
	 * @param type $alias 
	 */
	public function setDisplayImage($alias){
		//TODO: Content API => set display image
	}
}
?>