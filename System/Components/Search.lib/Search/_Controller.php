<?php
abstract class _Search_Controller
	implements Search_Interface_ActiveController
{
	protected $request = null,
			  $value = null,
			  $searchId = null;
	protected $elements = array(),
			  $keywords = array(),
			  $required = array(),
			  $vetoed = array();

	protected function currentSection(){
		//remove the "Search_Controller_" prefix
		return substr(get_class($this), 18);
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
				case Search_Request_Element::MUST_HAVE:
					$this->keywords[] = $value;
					$this->required[] = $value;
					break;
				case Search_Request_Element::MAY_HAVE:
					$this->keywords[] = $value;
					break;
				case Search_Request_Element::MUST_NOT_HAVE:
					$this->vetoed[] = $value;
					break;
			}
		}
	}

	public function setRequest(Search_Request $request){
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

	public function gather() {
		if(!Core::Database()->hasQueryForClass('gather', $this)){
			return;
		}
		$dba = Core::Database()
				->createQueryForClass($this)
				->call('gather');
		foreach ($this->keywords as $criteria){
			$criteria = $this->gatherValue($criteria);
			if($criteria){
				$dba->withParameters($this->searchId, $criteria)->executeInsert();
			}
		}
	}

	public function filter(){
		$rules = array(
			'Require' => &$this->required,
			'Veto'    => &$this->vetoed
		);
		$db = Core::Database();
		$dba = $db->createQueryForClass($this);
		foreach ($rules as $filter => $elements){
			$query = 'filter'.$filter;
			if($db->hasQueryForClass($query, $this)){
				$dba = $dba->call($query);
				foreach ($elements as $criteria){
					$criteria = $this->filterValue($criteria);
					if($criteria){
						$dba->withParameters($this->searchId, $criteria)->execute();
					}
				}
			}
		}
	}
	
	public function rate(){}

	public function order(){
		if(!Core::Database()->hasQueryForClass('order', $this)){
			return;
		}
		if(Core::Database()->hasQueryForClass('initOrdering', $this)){
			Core::Database()
				->createQueryForClass($this)
				->call('initOrdering')
				->withoutParameters()
				->execute();
		}
		Core::Database()
			->createQueryForClass($this)
			->call('order')
			->withParameters($this->searchId, $this->searchId)
			->execute();
	}
}
?>
