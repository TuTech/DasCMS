<?php
class ContentProxy_DescriptionController
{
	protected $proxy, $content;

	public function __construct(ContentProxyController $proxy) {
		$this->proxy = $proxy;
		$this->content = $proxy->_content();
	}
	
	/**
	 * content title
	 * @return type 
	 */
	public function title(){
		return $this->content->getTitle();
	}
	
	/**
	 * content subtitle
	 * @return type 
	 */
	public function subtitle(){
		return $this->content->getSubTitle();
	}
	
	/**
	 * description of content
	 * @return type 
	 */
	public function description(){
		return $this->content->getDescription();
	}
	
	/**
	 * set new title
	 * @param type $newTitle 
	 */
	public function setTitle($newTitle){
		$this->content->setTitle($newTitle);
	}
	
	/**
	 * set new subtitle
	 * @param type $newSubtitle 
	 */
	public function setSubtitle($newSubtitle){
		$this->content->setSubTitle($newSubtitle);
	}
	
	/**
	 * set new description
	 * @param type $newDescription 
	 */
	public function setDescription($newDescription){
		$this->content->setDescription($newDescription);
	}
}
?>