<?php
class ContentProxy_ConnectionController
{
	protected $proxy, $content;

	public function __construct(ContentProxyController $proxy) {
		$this->proxy = $proxy;
		$this->content = $proxy->_content();
	}
	
	/**
	 * weak refernces
	 */
	public function references(){
		//TODO: Content API => references
	}
	
	/**
	 * strong references - preventing deletion
	 */
	public function bindings(){
		//TODO: Content API => bindings 
	}
	
	/**
	 * add weak ref
	 * @param type $other 
	 */
	public function addReference($other){
		//TODO: Content API => add reference
	}
	
	/**
	 * add strong ref
	 * @param type $other 
	 */
	public function addBinding($other){
		//TODO: Content API => add binding
	}
	
	/**
	 * remove weak ref
	 * @param type $other 
	 */
	public function removeReference($other){
		//TODO: Content API => remove reference
	}
	
	/**
	 * remove strong ref
	 * @param type $other 
	 */
	public function removeBinding($other){
		//TODO: Content API => remove binding
	}
	
	/**
	 * remove all refences
	 * to allow force-delete
	 */
	public function clearAllReferences(){
		//TODO: Content API => clear references
	}
}
?>