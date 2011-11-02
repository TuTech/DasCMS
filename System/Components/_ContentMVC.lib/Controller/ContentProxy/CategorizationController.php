<?php
class ContentProxy_CategorizationController
{
	protected $proxy, $content;

	public function __construct(ContentProxyController $proxy) {
		$this->proxy = $proxy;
		$this->content = $proxy->_content();
	}
	
	/**
	 * get the tags
	 */
	public function tags(){
		return $this->content->getTags();
	}
	
	/**
	 * set new tags
	 * @param type $newTags 
	 */
	public function setTags($newTags){
		$this->content->setTags($newTags);
	}
	
	/**
	 * check if content has all $tags
	 * @param type $tags 
	 */
	public function hasTags($tags){
		//TODO: Content API => has tags
	}
}
?>