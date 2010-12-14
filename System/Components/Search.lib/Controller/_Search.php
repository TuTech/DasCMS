<?php
abstract class _Controller_Search
	implements Interface_Search_ActiveController
{
	protected $request = null,
			  $value = null,
			  $searchId = null;
	protected $elements = array(),
			  $keywords = array(),
			  $required = array(),
			  $vetoed = array();

	protected function currentSection(){
		return substr(get_class($this), strlen(Controller_Search_LabelResolver::CONTROLLER_PREFIX));
	}

	public function setSearchId($id){
		$this->searchId = $id;
	}

	protected function parseRequest(){
		//split data
		foreach ($this->elements as $element){
			$mod = $element->getModifier();
			$value = $element->getValue();
			switch ($mod){
				case Model_Search_RequestElement::MUST_HAVE:
					$this->keywords[] = $value;
					$this->required[] = $value;
					break;
				case Model_Search_RequestElement::MAY_HAVE:
					$this->keywords[] = $value;
					break;
				case Model_Search_RequestElement::MUST_NOT_HAVE:
					$this->vetoed[] = $value;
					break;
			}
		}
	}

	public function setRequest(Model_Search_Request $request){
		$this->request = $request;
		//get elements for this class
		$sect = $this->currentSection();
		if($request->hasSection($sect)){
			$this->elements = $request->getElements($sect);
		}
		$this->parseRequest();
	}

	protected function gatherValue($string){
		return $string;
	}

	protected function filterValue($string){
		return $string;
	}

	protected function classForQuery(){
		return $this;
	}


	public function gather() {

		if(!Core::Database()->hasQueryForClass('gather', $this)){
			return;
		}
		$converted = $this->convertToGatherValues($this->keywords);
		//gather
		$dba = Core::Database()
				->createQueryForClass($this->classForQuery())
				->call('gather');
		foreach ($converted as $criteria){
			$dba->withParameters($this->searchId, $criteria)->executeInsert();
		}
	}

	public function filter(){
		$rules = array(
			'Require' => $this->convertToFilterValues($this->required),
			'Veto'    => $this->convertToFilterValues($this->vetoed)
		);
		$db = Core::Database();
		$dba = $db->createQueryForClass($this->classForQuery());
		foreach ($rules as $filter => $elements){
			$query = 'filter'.$filter;
			if($db->hasQueryForClass($query, $this)){
				$dba = $dba->call($query);
				foreach ($elements as $criteria){
					$dba->withParameters($this->searchId, $criteria)->execute();
				}
			}
		}
	}
	
	protected function convertToGatherValues(&$values){
		$converted = array();
		foreach ($values as $criteria){
			$criteria = $this->gatherValue($criteria);
			if($criteria){
				$converted[] = $criteria;
			}
		}
		return $converted;
	}

	protected function convertToFilterValues(&$values){
		$converted = array();
		foreach ($values as $criteria){
			$criteria = $this->filterValue($criteria);
			if($criteria){
				$converted[] = $criteria;
			}
		}
		return $converted;
	}

	public function rate(){}

	public function order(){
		if(!Core::Database()->hasQueryForClass('order', $this)){
			return;
		}
		if(Core::Database()->hasQueryForClass('initOrdering', $this)){
			Core::Database()
				->createQueryForClass($this->classForQuery())
				->call('initOrdering')
				->withoutParameters()
				->execute();
		}
		Core::Database()
			->createQueryForClass($this->classForQuery())
			->call('order')
			->withParameters($this->searchId, $this->searchId)
			->execute();
	}
}
?>
