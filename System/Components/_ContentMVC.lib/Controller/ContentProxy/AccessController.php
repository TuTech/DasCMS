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
		//TODO: Content API => aliases 
	}
	
	/**
	 * allow users to add custom alias
	 * @param type $alias 
	 */
	public function addAlias($alias){
		//TODO: Content API => add alias
	}

		/**
	 * has this content this alias
	 * @param type $alias 
	 */
	public function hasAlias($alias){
		//TODO: Content API => has alias
	}

	/**
	 * date of publication
	 */
	public function pubDate(){
		return $this->content->getPubDate();
	}

	/**
	 * date of revokation
	 */
	public function revokeDate(){
		return $this->content->getRevokeDate();
	}

	/**
	 * is it public
	 * db public flag: isPublic != pubDate <= now < revokeDate 
	 */
	public function isPublic(){
		//TODO: Content API => is public
	}

	/**
	 * publish now or at $date
	 * @param type $date 
	 */
	public function publish($date = null){
		return $this->content->setPubDate($date);
	}
	
	/**
	 * revoke now or at $date
	 * @param type $date 
	 */
	public function revoke($date = null){
		return $this->content->setRevokeDate($date);
	}
}
?>