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

	/**
	 * @return Search_Engine
	 */
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
		return new Search_Result($this->runQuery($request));
	}

	protected function getSearchId($hash){
		return Core::Database()
			->createQueryForClass($this)
			->call('getId')
			->withParameters($hash)
			->fetchSingleValue();
	}

	protected function runQuery(Search_Request $request){
		//generate search id
		$hash = $request->getHashCode();

		//check for cached result for hash
		$searchId = $this->getSearchId($hash);

		//no cache - try to create
		if(!$searchId){
			try{
				$searchId = Core::Database()
					->createQueryForClass($this)
					->call('createQuery')
					->withParameters(strval($request), $hash)
					->executeInsert();
				if($searchId){
					//our search! Fill it
					$this->executeSearch($searchId, $request);
				}
			}
			catch(Exception $e){
				SErrorAndExceptionHandler::reportException($e);
				$searchId = null;
			}
		}

		//perhaps a conflicting create.... retry to get id
		if(!$searchId){
			$searchId = $this->getSearchId($hash);
		}

		if(!$searchId){
			throw new Exception('failed to initialize query');
		}

		return $searchId;
	}

	protected function executeSearch($searchId, Search_Request $request){
	    //load controller objects
		$controllers = array();
		$startTime = microtime(true);
		$index = -1;
		foreach($request->getSections() as $controllerName){
			$class = Search_Parser::CONTROLLER_PREFIX.$controllerName;
			$controllers[++$index] = new $class();
			$controllers[$index]->setSearchId($searchId);
			$controllers[$index]->setRequest($request);
		}

		//run query
		foreach(array('gather', 'filter', 'rate') as $action){
			foreach ($controllers as $nso){
				$nso->{$action}();
			}
		}

		//assign item numbers to elements based on score

		//set runtime
		$endTime = microtime(true);
		Core::Database()
			->createQueryForClass($this)
			->call('setRuntime')
			->withParameters(floor(($endTime-$startTime)*1000), $searchId)
			->execute();
	}
}
?>
