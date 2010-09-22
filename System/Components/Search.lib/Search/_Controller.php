<?php
abstract class _Search_Controller
	implements Search_Controller
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

	public function gather(){}
	public function filter(){}
	public function rate(){}
}
?>
