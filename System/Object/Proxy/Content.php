<?php
class Proxy_Content extends _Proxy
{
	
	protected static $contents = array();
	
	public static function create(BContent $content)
	{
		//FIXME handle access
		$id = $content->getId();
		if(!isset(self::$contents[$id]))
		{
			self::$contents[$id] = new Proxy_Content($content);
		}
		return self::$contents[$id];
	}
	
	/**
	 * @var BContent
	 */
	protected $content;
	
	protected function __construct(BContent $content)
	{
		$this->content = $content;
	}
	
	/**
	 * function mapper
	 * @param string $function
	 * @return mixed
	 */
	public function __call($function)
	{
		if(substr($function,0,3) == 'get'
			&& method_exists ($this->content, $function))
		{
			return $this->content->{$function}();
		}
		return null;
	}
	
	/**
	 * property check
	 * @param string $var
	 * @return bool
	 */
	public function __isset($var)
	{
		return method_exists ($this->content, 'get'.ucfirst($var));
	}	
	
	/**
	 * property mapper 
	 * @param string $var
	 * @return mixed
	 */
	public function __get($var)
	{
		return $this->__call('get'.ucfirst($var));
	}
	
	/**
	 * @param string $interface
	 * @return bool
	 */
	public function implementsInterface($interface)
	{
		return $this->content instanceof $interface;
	}
	
	/**
	 * @param string $composite
	 * @return bool
	 */
	public function implementsComposite($composite)
	{
		return $this->content->hasComposite($composite);
	}
}
?>