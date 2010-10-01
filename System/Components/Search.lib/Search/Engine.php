<?php
class Search_Engine
	implements Search_Interface_OrderingDelegate
{
	/*==Search_Engine==
	 * +getInstance() => returns cloned instance
	 * -runQuery(queryString, itemsPerPage, orderBy, asc|desc) => Serch_Query
	 */

	const SORT_ORDER_ASC = true;
	const SORT_ORDER_DESC = false;

	private static $instance;
	private $orderingDelegate;
	private function  __clone() {}

	/**
	 * constructor
	 */
	private function  __construct() {
		$this->orderingDelegate = $this;
	}

	/**
	 * change delegate for ordering
	 * @param Search_Interface_OrderingDelegate $delegate
	 */
	public function setOrderingDelegate(Search_Interface_OrderingDelegate $delegate){
		$this->orderingDelegate = $delegate;
	}

	/**
	 * singleton
	 * @return Search_Engine
	 */
	public static function getInstance(){
		if(self::$instance == null){
			self::$instance = new Search_Engine();
		}
		return self::$instance;
	}

	/**
	 * remove all cached searches
	 */
	public function flush(){
		Core::Database()
			->createQueryForClass($this)
			->call('flush')
			->withoutParameters()
			->execute();
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
		$rewriters = Core::getClassesWithInterface('Search_Interface_Rewriter');
		foreach ($rewriters as $rewriteClass){
			$rwObject = new $rewriteClass();
			if($rwObject instanceof Search_Interface_Rewriter){
				$rwObject->rewriteSearchRequest($request);
			}
		}
		return new Search_Result($this->runQuery($request));
	}

	/**
	 * resolve hash to id
	 * @param string $hash
	 * @return int
	 */
	protected function getSearchId($hash){
		return Core::Database()
			->createQueryForClass($this)
			->call('getId')
			->withParameters($hash)
			->fetchSingleValue();
	}

	/**
	 * load cached query or start it
	 * @param Search_Request $request
	 * @return int
	 */
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
		
		//dump results
		$res = Core::Database()
			->createQueryForClass($this)
			->call('dump')
			->withParameters($searchId);
		$i = 0;
		while($row = $res->fetchResult()){
			printf("Nr.: %d\n%s\n%s\n[%s]\n%s\n--\n", ++$i, $row[0], $row[1], $row[2], $row[3]);
		}
		$res->free();

		
		return $searchId;
	}

	/**
	 * perform the actual search
	 * @param int $searchId
	 * @param Search_Request $request
	 */
	protected function executeSearch($searchId, Search_Request $request){
	    //load controller objects
		$controllers = array();
		$startTime = microtime(true);
		$index = -1;
		foreach($request->getSections() as $controllerName){
			$class = Search_LabelResolver::getInstance()->controllerToClass($controllerName);
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
		$this->orderingDelegate->order();

		//set runtime
		$endTime = microtime(true);
		Core::Database()
			->createQueryForClass($this)
			->call('setStats')
			->withParameters($endTime-$startTime, $searchId, $searchId)
			->execute();
	}

	/**
	 * default ordering delegate for search mode
	 */
	public function order() {
		//loop through controllers and let them rate
		//assign item nrs to elements based on the rated
	}
}
?>