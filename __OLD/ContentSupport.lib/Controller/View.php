<?php
class Controller_View
{
	private static $instance;
	private function __clone() {}
	private function __construct() {}

	/**
	 * @return Controller_View
	 */
	public static function getInstance(){
		if(!self::$instance){
			self::$instance = new Controller_View();
		}
		return self::$instance;
	}

	/**
	 * list of stored views
	 * @return array
	 */
	public function getStoredViews(){
		return Core::Database()
			->createQueryForClass($this)
			->call('list')
			->withoutParameters()
			->fetchList();
	}

	/**
	 * @param string $name
	 * @param Interface_AcceptsContent $object
	 * @return bool success
	 */
	public function storeView($name, Interface_AcceptsContent $object){
		$data = 'base64:'.base64_encode(serialize($object));
		return !!Core::Database()
			->createQueryForClass($this)
			->call('set')
			->withParameters($data, $name, $data)
			->execute();
	}

	/**
	 * @param string $name
	 * @return bool success
	 */
	public function deleteView($name){
		return !!Core::Database()
			->createQueryForClass($this)
			->call('del')
			->withParameters($name)
			->execute();
	}

	/**
	 * @param string $name
	 * @return bool
	 */
	public function hasView($name){
		return !!Core::Database()
			->createQueryForClass($this)
			->call('exists')
			->withParameters($name)
			->fetchSingleValue();
	}

    /**
     * @param string $data
     * @return Interface_AcceptsContent
     */
    public function loadView($name)
    {
        //reverse evil
		$data = Core::Database()
			->createQueryForClass($this)
			->call('load')
			->withParameters($name)
			->fetchSingleValue();
		if(empty ($data)){
			throw new FileNotFoundException('formatter not found',$name);
		}
		if(substr($data,0,7) == 'base64:'){
			$data = base64_decode(substr($data,7));
		}
        $container = unserialize($data);
        return $container;
    }

    /**
     * @param string $data
     * @param Interface_Content $content
     * @return Interface_AcceptsContent
     */
    public function display(Interface_Content $content, $withView)
    {
		$obj = $this->loadView($withView);
		if($obj instanceof Interface_AcceptsContent){
			$obj->acceptContent($content);
		}
        return $obj;
    }
}
?>