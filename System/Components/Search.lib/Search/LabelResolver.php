<?php
class Search_LabelResolver implements IShareable{
	const DEFAULT_LABEL = 'global';
	const CONTROLLER_PREFIX = 'Search_Controller_';
	const LABEL_PREFIX = 'Search_Label_';

	/**
	 * @var Search_LabelResolver
	 */
	private static $instance;

	/**
	 * @return Search_LabelResolver
	 */
	public static function getInstance(){
		if(!self::$instance){
			self::$instance = new Search_LabelResolver();
		}
		return self::$instance;
	}

	/**
	 * get a list of all controllers for the given name
	 * @param string $name
	 * @return array
	 */
	public function getControllersForLabel($name){
		$interface = $this->labelToInterface($name);
		$controllers = array();

		$classes = Core::getClassesWithInterface($interface);
		foreach ($classes as $class){
			$controller = $this->classToController($class);
			if($controller){
				$controllers[] = $controller;
			}
		}
		return $controllers;
	}

	/**
	 * get the interface name for $label or the global interface
	 * @param string $label
	 * @return string name
	 */
	public function labelToInterface($label){
		$interface = self::LABEL_PREFIX.ucfirst(strtolower(rtrim($label,':')));
		if(!Core::classExists($interface)){
			$interface = self::LABEL_PREFIX.ucfirst(strtolower(self::DEFAULT_LABEL));
		}
		return  $interface;
	}

	/**
	 * get the controller name or null
	 * @param string $className
	 * @return string|null
	 */
	public function classToController($className){
		if(substr($className, 0, strlen(self::CONTROLLER_PREFIX)) == self::CONTROLLER_PREFIX){
			return substr($className, strlen(self::CONTROLLER_PREFIX));
		}
		return null;
	}

	public function controllerToClass($controller){
		return self::CONTROLLER_PREFIX.$controller;
	}
}
?>
