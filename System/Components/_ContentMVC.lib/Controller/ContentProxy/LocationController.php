<?php
class ContentProxy_LocationController extends Content_LocationController
{
	protected $proxy, $content;

	public function __construct(ContentProxyController $proxy) {
		$this->proxy = $proxy;
		$this->content = $proxy->_content();
	}
	
	public function address(){
		//TODO Content API => address
	}
	
	public function setAddress(){
		//TODO Content API => set address
	}
	
	public function latitude(){
		//TODO Content API => latitude
	}
	
	public function setLatitude(){
		//TODO Content API => set latitude
	}
	
	public function longitude(){
		//TODO Content API => longitude
	}
	
	public function setLongitude(){
		//TODO Content API => set longitude
	}
	
	/**
	 * radius of the selected view in meters
	 */
	public function radius(){
		//compute map zoom based on map size and display radius
		//TODO Content API => map-radius
	}

	public function setRadius(){
		//TODO Content API => set map-radius
	}
}
?>