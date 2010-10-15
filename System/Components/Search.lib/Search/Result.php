<?php
class Search_Result
	implements
		Search_Interface_Resultset,
		Search_Interface_ConfiguredResultset,
		Search_Interface_ResultPage
{
	//search reference
	protected $searchId;

	//the meta info for this search
	protected $resultCount, $runTime, $created;

	//api vars
	protected $itemsPerPage, $pageNr, $order = Search_Interface_ConfiguredResultset::ASC;

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
	//Search_Interface_Resultset

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
	 * @return Search_Interface_ConfiguredResultset
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
	//Search_Interface_ConfiguredResultset

	/**
	 * @param int $pageNr
	 * @return Search_Interface_ResultPage
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
		$this->ordered(Search_Interface_ConfiguredResultset::ASC);
		return $this;
	}

	public function inDescendingOrder() {
		$this->ordered(Search_Interface_ConfiguredResultset::DESC);
		return $this;
	}

	public function ordered($ascOrDesc) {
		if($ascOrDesc != Search_Interface_ConfiguredResultset::ASC
				&& $ascOrDesc != Search_Interface_ConfiguredResultset::DESC)
		{
			throw new XArgumentException('argument not a valid ASC or DESC value');
		}
		$this->order = $ascOrDesc;
		return $this;
	}

	/////////////////////////////
	//Search_Interface_ResultPage

	/**
	 * @return array string[]
	 */
	public function asAliases() {
		//compute range of items
		$firstElement = 1;
		$lastElement = 1;
		$listAscending = $this->order == Search_Interface_ConfiguredResultset::ASC;

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
		return $list;
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
}
?>
