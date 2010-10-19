<?php
class Model_Search_Result
	implements
		Interface_Search_Resultset,
		Interface_Search_ConfiguredResultset,
		Interface_Search_ResultPage
{
	//search reference
	protected $searchId;

	//the meta info for this search
	protected $resultCount, $runTime, $created;

	//api vars
	protected $itemsPerPage, $pageNr, $order = Interface_Search_ConfiguredResultset::ASC;

	//cache
	private $fetched;

	/**
	 * constructor
	 * @param int $searchId
	 */
	public function __construct($searchId) {
		$this->searchId = $searchId;

		//loading meta data
		$res = Core::Database()
			->createQueryForClass($this)
			->call('meta')
			->withParameters($searchId);
		list($this->runTime, $this->resultCount, $this->created) = $res->fetchResult();
		$res->free();
		
		//LATER: while not finished wait 50ms or break after 1s and report timeout; delete offending query?
	}

	/**
	 * dumps all found aliases
	 * @return string
	 */
	public function  __toString() {
		$all = $this->getResultCount();
		$aliases = $this->fetch($all)->resultsFromPage(1)->asAliases();
		$str = "";
		foreach($aliases as $alias){
			$str .= sprintf("    %s\n", $alias);
		}
		return $str;
	}

	////////////////////////////
	//Interface_Search_Resultset

	/**
	 * @return float
	 */
	public function getExecutionTime() {
		return $this->runTime;
	}

	/**
	 * calculate the available pages with $nrOfItems items on each page
	 * @param int $nrOfItems
	 * @return int
	 */
	public function getPageCountFor($nrOfItems) {
		// 1 <= $nrOfItems <= $this->resultCount
		$itemsPerPage = min($this->resultCount, max(1, $nrOfItems));

		//return page 1 even if there are no results
		return max(1, ceil($this->resultCount/$itemsPerPage)); 
	}

	/**
	 * get the found item count
	 * @return int
	 */
	public function getResultCount() {
		return $this->resultCount;
	}

	/**
	 * @param int $nrOfItems
	 * @return Interface_Search_ConfiguredResultset
	 */
	public function fetch($nrOfItems) {
		if($nrOfItems < 1){
			throw new OutOfRangeException("you must request at least 1 item per page");
		}
		// 1 <= $nrOfItems <= $this->resultCount
		$this->itemsPerPage = min($this->resultCount, $nrOfItems);
		return $this;
	}

	//////////////////////////////////////
	//Interface_Search_ConfiguredResultset

	/**
	 * @param int $pageNr
	 * @return Interface_Search_ResultPage
	 */
	public function resultsFromPage($pageNr) {
		if(!$this->itemsPerPage){
			throw new Exception('you must call fetch() before resultsFromPage()');
		}
		if($pageNr > $this->getPageCountFor($this->itemsPerPage)){
			throw new OutOfBoundsException("the requested page does not exist");
		}
		$this->pageNr = $pageNr;
		return $this;
	}

	public function inAscendingOrder() {
		$this->ordered(Interface_Search_ConfiguredResultset::ASC);
		return $this;
	}

	public function inDescendingOrder() {
		$this->ordered(Interface_Search_ConfiguredResultset::DESC);
		return $this;
	}

	public function ordered($ascOrDesc) {
		if($ascOrDesc != Interface_Search_ConfiguredResultset::ASC
				&& $ascOrDesc != Interface_Search_ConfiguredResultset::DESC)
		{
			throw new XArgumentException('argument not a valid ASC or DESC value');
		}
		$this->order = $ascOrDesc;
		return $this;
	}

	/////////////////////////////
	//Interface_Search_ResultPage

	/**
	 * @return array string[]
	 */
	public function asAliases() {
		if(!$this->fetched){
			//compute range of items
			$firstElement = 1;
			$lastElement = 1;
			$listAscending = $this->order == Interface_Search_ConfiguredResultset::ASC;

			if($listAscending){
				$skipping = $this->itemsPerPage * ($this->pageNr - 1);
				//page 1: (1-1)*10 = 0
				//page 2: (2-1)*10 = 10
			}
			else{
				$skipping = $this->resultCount - $this->pageNr * $this->itemsPerPage;
				//page 1: 123 - 1 * 10 = 113
				//page 2: 123 - 2 * 10 = 103
			}
			$firstElement = 1 + $skipping;
			//page 1: 1+0  = 1
			//page 2: 1+10 = 11

			$lastElement = $this->itemsPerPage + $skipping;
			//page 1: 10+0  = 10
			//page 2: 10+10 = 20

			//for select between X an Y
			$list = Core::Database()
				->createQueryForClass($this)
				->call('page')
				->withParameters($this->searchId, $firstElement, $lastElement)
				->fetchList();

			if(!$listAscending){
				//order result page descending
				$list = array_reverse($list);
			}
			$this->fetched = $list;
		}
		return $this->fetched;
	}

	/**
	 * @return array Interface_Content[]
	 */
	public function asContents() {
		$ret = array();
		$aliases = $this->asAliases();
		$ctrl = Controller_Content::getInstance();
		foreach ($aliases as $alias){
			$ret[] = $ctrl->accessContent($alias, $this);
		}
		return $ret;
	}

	/**
	 * get max page
	 * @return int
	 */
	public function getLastPageNumber(){
		return $this->getPageCountFor($this->itemsPerPage);
	}

	/**
	 * get the defined element count
	 * @return int
	 */
	public function getPageElementCount(){
		return $this->itemsPerPage;
	}

	/**
	 * get the item count for this page
	 * @return int
	 */
	public function getCurrentElementCount(){
		return count($this->asAliases());
	}

	/**
	 * get the item count for this page
	 * @return int
	 */
	public function getTotalElementCount(){
		return $this->resultCount;
	}

	/**
	 * current page nr
	 * @return int
	 */
	public function getPageNumber(){
		return $this->pageNr;
	}

	/**
	 * @return bool
	 */
	public function isFirstPage(){
		return $this->pageNr == 1;
	}

	/**
	 * @return bool
	 */
	public function isLastPage(){
		return $this->getLastPageNumber() == $this->pageNr;
	}
}
?>
