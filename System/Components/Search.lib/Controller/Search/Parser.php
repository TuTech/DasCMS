<?php
class Controller_Search_Parser
	implements Interface_Singleton
{
	const EXTRACTOR = '/((([a-zA-Z]+):)?(\\+|-)?("([^"\\\\]*(\\\\.[^"\\\\]*)*)"|([\\S]+)))/u';
	protected static $instance = null;
	protected $resolver;

	/**
	 * @return Controller_Search_Parser
	 */
	public static function getInstance(){
		if(!self::$instance){
			self::$instance = new Controller_Search_Parser();
		}
		return self::$instance;
	}

	private function  __construct() {
		$this->resolver = Controller_Search_LabelResolver::getInstance();
	}

	/**
	 * parses string data into a Model_Search_Request object
	 * @param string $str
	 * @return Model_Search_Request
	 */
	public function parse($str){
		$request = new Model_Search_Request();
		if(preg_match_all(self::EXTRACTOR, $str, $matches, PREG_SET_ORDER)){
			foreach ($matches as $match){
				//get the match
				$label = empty($match[2]) ? Controller_Search_LabelResolver::DEFAULT_LABEL : $match[2];
				$modifier = empty($match[4])
					? Model_Search_RequestElement::MAY_HAVE
					: ($match[4] == '+'
							? Model_Search_RequestElement::MUST_HAVE//modifier: +
							: Model_Search_RequestElement::MUST_NOT_HAVE);//modifier: -
				$value = !empty($match[6]) ? $match[6] : $match[5];

				//assign to fully qualified controller name
				foreach ($this->resolver->getControllersForLabel($label) as $controller){
					if(!$request->hasSection($controller)){
						$request->addSection($controller);
					}
					$request->addRequestElement($controller, $request->createRequestElement($value, $modifier));
				}
			}
		}
		return $request;
	}
}
?>