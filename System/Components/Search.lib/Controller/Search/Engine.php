<?php
class Controller_Search_Engine implements Interface_Singleton
{
	/*==Controller_Search_Engine==
	 * +getInstance() => returns cloned instance
	 * -runQuery(queryString, itemsPerPage, orderBy, asc|desc) => Serch_Query
	 */

	const SORT_ORDER_ASC = true;
	const SORT_ORDER_DESC = false;

	private static $instance;
	private $orderingDelegate;
	private $parser;
	private function  __clone() {}

	/**
	 * constructor
	 */
	private function  __construct() {
		$this->orderingDelegate = $this;
		$this->parser = Controller_Search_Parser::getInstance();
	}

	/**
	 * change delegate for ordering
	 * @param Interface_Search_OrderingDelegate $delegate
	 */
	public function setOrderingDelegate(Interface_Search_OrderingDelegate $delegate){
		$this->orderingDelegate = $delegate;
	}

	/**
	 * singleton
	 * @return Controller_Search_Engine
	 */
	public static function getInstance(){
		if(self::$instance == null){
			self::$instance = new Controller_Search_Engine();
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
		Core::Database()
			->createQueryForClass($this)
			->call('flushResults')
			->withoutParameters()
			->execute();
	}

	/**
	 * @param string $queryString
	 * @param int $itemsPerPage
	 * @param Search_Order $orderBy
	 * @param bool $orderDirection Controller_Search_Engine::SORT_ORDER_ASC or Controller_Search_Engine::SORT_ORDER_DESC
	 * @return Interface_Search_Resultset
	 */
	public function query($queryString){
		//parse search input
		$request = $this->parser->parse($queryString);

		//run search modifiers
		$rewriters = Core::getClassesWithInterface('Interface_Search_Rewriter');
		foreach ($rewriters as $rewriteClass){
			$rwObject = new $rewriteClass();
			if($rwObject instanceof Interface_Search_Rewriter){
				$rwObject->rewriteSearchRequest($request);
			}
		}
		return new Model_Search_Result($this->runQuery($request));
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
	 * @param Model_Search_Request $request
	 * @return int
	 */
	protected function runQuery(Model_Search_Request $request){
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

	/**
	 * perform the actual search
	 * @param int $searchId
	 * @param Model_Search_Request $request
	 */
	protected function executeSearch($searchId, Model_Search_Request $request){
	    //load controller objects
		$controllers = array();
		$startTime = microtime(true);
		$index = -1;
		foreach($request->getSections() as $controllerName){
			$class = Controller_Search_LabelResolver::getInstance()->controllerToClass($controllerName);
			$controllers[++$index] = new $class();
			$controllers[$index]->setSearchId($searchId);
			$controllers[$index]->setRequest($request);
		}

		//run query
		foreach ($controllers as $nso){
			$nso->gather();
		}
		foreach ($controllers as $nso){
			$nso->filter();
		}
		foreach ($controllers as $nso){
			$nso->rate();
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