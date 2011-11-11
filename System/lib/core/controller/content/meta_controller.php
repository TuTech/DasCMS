<?php
class Content_MetaController
{
	public function __construct(ContentController $content) {
	}

	/**
	 * create date
	 */
	public function created(){
		
	}
	
	/**
	 * name of the user that created this file
	 */
	public function creator(){
		
	}
	
	/**
	 * last edit date
	 */
	public function lastEdit(){
		
	}
	
	/**
	 * name of the user that edited this file last
	 */
	public function lastEditor(){
		
	}
	
	/**
	 * size in byte
	 */
	public function size(){
		
	}

	/**
	 * mimetype
	 */
	public function mimetype(){
		
	}
	
	/**
	 * checksum of this file
	 * (for caches/etag etc)
	 */
	public function shasum(){
		
	}

	/**
	 * user-ref to current owner or null
	 */
	public function owner(){
		
	}
	
	/**
	 * group-ref to a usergroup 
	 * never null to prevent abandoned files (strong binding)
	 */
	public function group(){
		
	}
	
	/**
	 * not visible in the open dialog 
	 * managed via another content
	 * @return bool
	 */
	public function isInternal(){
		
	}
}
?>