<?php
class ContentProxy_ConnectionController
{
	protected $proxy;

	public function __construct(ContentProxyController $proxy) {
		$this->proxy = $proxy;
	}
	
	/**
	 * weak refernces
	 */
	public function references(){
		
	}
	
	/**
	 * strong references - preventing deletion
	 */
	public function bindings(){
		
	}
	
	/**
	 * add weak ref
	 * @param type $other 
	 */
	public function addReference($other){
		
	}
	
	/**
	 * add strong ref
	 * @param type $other 
	 */
	public function addBinding($other){
		
	}
	
	/**
	 * remove weak ref
	 * @param type $other 
	 */
	public function removeReference($other){
		
	}
	
	/**
	 * remove strong ref
	 * @param type $other 
	 */
	public function removeBinding($other){
		
	}
	
	/**
	 * remove all refences
	 * to allow force-delete
	 */
	public function clearAllReferences(){
		
	}
}
?>