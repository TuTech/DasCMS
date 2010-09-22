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
	public function query($queryString){
		//parse search input
		$parser = Search_Parser::getInstance();
		$request = $parser->parse($queryString);

		//run search modifiers
		$rewriters = Core::getClassesWithInterface('Search_Rewriter');
		foreach ($rewriters as $rewriteClass){
			$rwObject = new $rewriteClass();
			if($rwObject instanceof Search_Rewriter){
				$rwObject->rewriteSearchRequest($request);
			}
		}

		//generate search id
		$hash = $request->getHashCode();

		//check for cached result for hash
		if(!$this->hasCachedQuery($hash)){

			//make db entry and get search id
			$searchId = $this->createQuery($request);

			//init controllers
			foreach ($controllers as $ns => $nso){
				if($nso instanceof Search_Controller){
					$nso->setRequest($request);
					$nso->setSearchId($searchId);
				}
			}

			//run query
			foreach(array('gather', 'filter', 'rate') as $action){
				foreach ($controllers as $ns => $nso){
					$nso->{$action}();
				}
			}
		}

		return new Search_Result($hash);
	}

	protected function hasCachedQuery($hash){
		//DB Query
	}

	protected function createQuery(Search_Request $request){
		$normalizedQuery = strval($request);
		$hash = $request->getHashCode();
		return null;
	}
}
?>
