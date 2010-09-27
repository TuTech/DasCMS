<?php
class Search_Parser
{
	const DEFAULT_LABEL = 'global';
	const CONTROLLER_PREFIX = 'Search_Controller_';
	const LABEL_PREFIX = 'Search_Label_';
	const EXTRACTOR = '/((([a-zA-Z]+):)?(\\+|-)?("([^"\\\\]*(\\\\.[^"\\\\]*)*)"|([\\S]+)))/u';
	protected static $instance = null;

	/**
	 * @return Search_Parser
	 */
	public static function getInstance(){
		if(!self::$instance){
			self::$instance = new Search_Parser();
		}
		return self::$instance;
	}

	/**
	 * parses string data into a Search_Request object
	 * @param string $str
	 * @return Search_Request
	 */
	public function parse($str){
		$request = new Search_Request();
		if(preg_match_all(self::EXTRACTOR, $str, $matches, PREG_SET_ORDER)){
			foreach ($matches as $match){
				//get the match
				$label = empty($match[2]) ? self::DEFAULT_LABEL : $match[2];
				$modifier = empty($match[4])
					? Search_Request_Element::MAY_HAVE
					: ($match[4] == '+'
							? Search_Request_Element::MUST_HAVE//modifier: +
							: Search_Request_Element::MUST_NOT_HAVE);//modifier: -
				$value = !empty($match[6]) ? $match[6] : $match[5];

				//assign to fully qualified controller name
				foreach ($this->getControllers($label) as $controller){
					if(!$request->hasSection($controller)){
						$request->addSection($controller);
					}
					$request->addRequestElement($controller, $request->createRequestElement($value, $modifier));
				}
			}
		}
		return $request;
	}

	/**
	 * get a list of all controllers for the given name
	 * @param string $name
	 * @return array
	 */
	protected function getControllers($name){
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
	protected function labelToInterface($label){
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
	protected function classToController($className){
		if(substr($className, 0, strlen(self::CONTROLLER_PREFIX)) == self::CONTROLLER_PREFIX){
			return substr($className, strlen(self::CONTROLLER_PREFIX));
		}
		return null;
	}

}
?>