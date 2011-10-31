<?php
class ContentProxy_AccessController
{
	protected $proxy, $content;

	public function __construct(ContentProxyController $proxy) {
		$this->proxy = $proxy;
		$this->content = $proxy->_content();
	}

	/**
	 * current alias
	 * @return type 
	 */
	public function alias(){
		return $this->content->getAlias();
	}
	
	/**
	 * all aliases
	 */
	public function aliases(){
		
	}
		
	/**
	 * has this content this alias
	 * @param type $alias 
	 */
	public function hasAlias($alias){
		
	}

	/**
	 * date of publication
	 */
	public function pubDate(){
		
	}

	/**
	 * date of revokation
	 */
	public function revokeDate(){
		
	}

	/**
	 * is it public
	 * db public flag: isPublic != pubDate <= now < revokeDate 
	 */
	public function isPublic(){
		
	}

	/**
	 * publish now or at $date
	 * @param type $date 
	 */
	public function publish($date = null){

	}
	
	/**
	 * revoke now or at $date
	 * @param type $date 
	 */
	public function revoke($date = null){
		
	}
}
?>