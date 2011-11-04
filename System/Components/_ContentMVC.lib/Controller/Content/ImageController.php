<?php
class Content_ImageController
{
	public function __construct(ContentController $content) {
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