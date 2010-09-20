<?php
class Search_Engine
{
	/*==Search_Engine==
	 * +getInstance() => returns cloned instance
	 * -runQuery(queryString, itemsPerPage, orderBy, asc|desc) => Serch_Query
	 */

	const SORT_ORDER_ASC = true;
	const SORT_ORDER_DESC = false;

	private static $instance;
	private function  __clone() {}
	private function  __construct() {}


	public static function getInstance(){
		if(self::$instance == null){
			self::$instance = new Search_Engine();
		}
		return self::$instance;
	}

	/**
	 * @param string $queryString
	 * @param int $itemsPerPage
	 * @param Search_Order $orderBy
	 * @param bool $orderDirection Search_Engine::SORT_ORDER_ASC or Search_Engine::SORT_ORDER_DESC
	 */
	public function query($queryString, $itemsPerPage, Search_Order $orderBy, $orderDirection){
		$parser = Search_Parser::getInstance();
		$request = $parser->parse($queryString);

		//resolve global controller

		//run search modifiers

		$hash = $parser->getQueryHash();

		//check for cached result for hash
		if(!$this->hasCachedQuery($hash)){
			//run search

			//register search id
			$searchId = null;

			//load controller objects
			$controllers = array();
			$nsPrefix = 'Search_Controller_';
			foreach($parser->getControllers() as $ns){
				$class = $nsPrefix.$ns;
				$controllers[$ns] = new $class();
				$controllers[$ns]->setSearchId($searchId);
			}

			//run query
			foreach(array('gather', 'filter', 'rate') as $action){
				foreach ($controllers as $ns => $nso){
					$nso->{$action}();
				}
			}
		}

		return new Search_Query($hash);
	}

	protected function hasCachedQuery($hash){

	}
}
?>
