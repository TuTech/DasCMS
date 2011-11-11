<?php
class Content_AccessController
{
	public function __construct(ContentController $content) {
	}

	/**
	 * current alias
	 * @return type 
	 */
	public function alias(){
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
		//TODO: Content API => is public
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