<?php
class ContentProxy_DataController extends Content_DataController
{
	protected $proxy, $content;

	public function __construct(ContentProxyController $proxy) {
		$this->proxy = $proxy;
		$this->content = $proxy->_content();
	}
	
	/**
	 * get the tags
	 */
	public function data(){
		return $this->content->getContent();
	}
	
	/**
	 * set new tags
	 * @param type $newTags 
	 */
	public function setData($newData){
		throw new Exception("cannot set data through proxy");
	}
}
?>