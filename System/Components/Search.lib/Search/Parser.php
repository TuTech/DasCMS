<?php
class Search_Parser
	implements Search_Interface_Parser, Interface_Singleton
{
	const EXTRACTOR = '/((([a-zA-Z]+):)?(\\+|-)?("([^"\\\\]*(\\\\.[^"\\\\]*)*)"|([\\S]+)))/u';
	protected static $instance = null;
	protected $resolver;

	/**
	 * @return Search_Parser
	 */
	public static function getInstance(){
		if(!self::$instance){
			self::$instance = new Search_Parser();
		}
		return self::$instance;
	}

	private function  __construct() {
		$this->resolver = Search_LabelResolver::getInstance();
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
				$label = empty($match[2]) ? Search_LabelResolver::DEFAULT_LABEL : $match[2];
				$modifier = empty($match[4])
					? Search_Request_Element::MAY_HAVE
					: ($match[4] == '+'
							? Search_Request_Element::MUST_HAVE//modifier: +
							: Search_Request_Element::MUST_NOT_HAVE);//modifier: -
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