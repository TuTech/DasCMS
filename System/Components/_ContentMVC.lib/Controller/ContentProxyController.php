<?php
class ContentProxyController
{
	//
	// Static
	//
	
	protected static $proxies = array();

	public static function proxy(Interface_Content $content){
		$id = $content->getId();
		if(!array_key_exists($id, self::$proxies)){
			self::$proxies[$id] = new ContentProxyController($content);
		}
		return self::$proxies[$id];
	}
	
	private function __clone() {}
	
	//
	// Object
	//
	
	protected $content;
	protected $access, $description, $meta, $connection, $categorization, $image;

	private function __construct(Interface_Content $content) {	
		$this->content = $content;
	}
	
	/**
	 * content access for sub proxies 
	 * @return Interface_Content 
	 */
	public function _content(){
		return $this->content;
	}

	public function guid(){
		return $this->content->getGUID();
	}
	
	public function description(){
		if(!$this->description){
			$this->description = new ContentProxy_DescriptionController($this);
		}
		return $this->description;		
	}
	
	public function access(){
		if(!$this->access){
			$this->access = new ContentProxy_AccessController($this);
		}
		return $this->access;
	}
	
	public function meta(){
		if(!$this->meta){
			$this->meta = new ContentProxy_MetaController($this);
		}
		return $this->meta;
	}
	
	public function connection(){
		if(!$this->connection){
			$this->connection = new ContentProxy_ConnectionController($this);
		}
		return $this->connection;
	}
	
	public function categorization(){
		if(!$this->categorization){
			$this->categorization = new ContentProxy_CategorizationController($this);
		}
		return $this->categorization;
	}
	
	public function image(){
		if(!$this->image){
			$this->image = new ContentProxy_ImageController($this);
		}
		return $this->image;
	}
}

?>