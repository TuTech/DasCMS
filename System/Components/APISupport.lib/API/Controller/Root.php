<?php
class API_Controller_Root
	extends _API_Controller_Array
{
	protected $controller;

	protected function getEntryPoints(){
		$points = array();
		$epc = Core::getClassesWithInterface('API_Interface_EntryPoint');
		foreach($epc as $class){
			$points[] = BObject::InvokeObjectByDynClass($class);
		}
		return $points;
	}

	protected function getElements() {
		$guids = array();
		foreach ($this->getEntryPoints() as $p){
			$guids[] = array($p->getControllerName());
		}
		return $guids;
	}

	protected function getKeys() {
		return array('guid');
	}

	protected function loadPath($path){
		$controller = $this;
		
		//load sub components
		if($path != '/'){
			$path = trim($path, " \t/");
			$path = preg_replace('/\/+/', '/', $path);
			$pathComponents = explode('/', $path);
			$entryPoint = array_shift($pathComponents);

			$guids = array();
			foreach ($this->getEntryPoints() as $p){
				if($entryPoint == $p->getControllerName()){
					$controller = $p->resolveSubPath($pathComponents);
					break;
				}
			}
		}
		$this->controller = $controller;
	}

	public function initWithPath($path){
		$this->loadPath($path);
		return $this;
	}

	public function handleMethod($method, $queryString, $data = null){
		if(!$this->controller){
			throw new Exception_HTTP(500);
		}
		$controller = $this->controller;
		switch (strtoupper($method)){
			case 'POST':
				if($controller instanceof API_Interface_AcceptsPost){
					return $controller->httpPost($data);
				}
				break;
			case 'HEAD':
			case 'GET':
				if($controller instanceof API_Interface_AcceptsGet){
					return $controller->httpGet($queryString);
				}
				break;
			case 'PUT':
				if($controller instanceof API_Interface_AcceptsPut){
					return $controller->httpPut($data);
				}
				break;
			case 'DELETE':
				if($controller instanceof API_Interface_AcceptsDelete){
					return $controller->httpDelete();
				}
		}
		//input not yet handled
		throw new Exception_HTTP(405);
	}
}
?>