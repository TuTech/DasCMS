<?php
class ContentProxy_ImageController
{
	protected $proxy;

	public function __construct(ContentProxyController $proxy) {
		$this->proxy = $proxy;
	}
	
	/**
	 * the appropriate icon for this content
	 */
	public function icon(){
		
	}
	
	/**
	 * the image version of the actual content 
	 * (e.g. a pdf2png rendering)
	 */
	public function contentImage(){
		
	}
	
	/**
	 * a custom image for this content
	 *  ==> old previewImage
	 */
	public function displayImage(){

	}
}
?>