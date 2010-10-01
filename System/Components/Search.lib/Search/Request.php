<?php
class Search_Request
{
	protected $data = array();
	protected $strval = null;


	//////////////////
	//section handling
	//////////////////
	
	public function getSections(){
		return array_keys($this->data);
	}

	public function addSection($section){
		$this->assertMissingSection($section);
		$this->data[$section] = array();
		$this->stateChanged();
	}

	public function removeSection($section){
		$this->assertSection($section);
		unset($this->data[$section]);
		$this->stateChanged();
	}

	public function hasSection($section){
		return array_key_exists($section, $this->data);
	}

	public function clearSection($section){
		$this->assertSection($section);
		$this->data[$section] = array();
		$this->stateChanged();
	}

	//////////////////
	//element handling
	//////////////////

	public function addRequestElement($section, Search_Request_Element $element){
		$this->assertSection($section);
		$this->data[$section][$element->getValue()] = $element;
		$this->stateChanged();
	}

	public function removeRequestElement($section, Search_Request_Element $element){
		$this->assertElement($section, $element->getId());
		unset($this->data[$section][$element->getValue()]);
		$this->stateChanged();
	}

	public function hasElement($section, Search_Request_Element $element){
		return (
			$this->hasDefinition($section, $element)
			&& $this->data[$section][$element->getValue()]->getId() == $element->getId()
		);
	}

	public function hasDefinition($section, Search_Request_Element $element){
		$this->assertSection($section);
		return array_key_exists($element->getValue(), $this->data[$section]);
	}

	/**
	 * @param string $section
	 * @return array Search_Request_Element[]
	 */
	public function getElements($section){
		$this->assertSection($section);
		return array_values($this->data[$section]);
	}

	/**
	 * @return Search_Request_Element
	 */
	public function getFirstElement($section){
		$this->assertSection($section);
		if(count($this->data[$section]) > 0){
			$copy = array_values($this->data[$section]);
			return $copy[0];
		}
		return null;
	}


	public function createRequestElement($value, $modifier = 0){
		return Search_Request_Element::create($value, $modifier);
	}

	////////////////
	//request status
	////////////////

	protected function stateChanged(){
		$this->strval = null;
	}

	public function  __toString() {
		if($this->strval == null){
			$str = '';
			foreach ($this->data as $section => $elements){
				foreach ($elements as $element){
					$str = sprintf('%s%s:%s ', $str, $section, $element);
				}
			}
			$this->strval = trim($str);
		}
		return $this->strval;
	}

	public function getHashCode(){
		return sha1(strval($this));
	}

	/**
	 * add data from $request if it is not already defined
	 * @param Search_Request $request
	 */
	public function apply(Search_Request $request){
		foreach ($request->getSections() as $section){
			if(!$this->hasSection($section)){
				$this->addSection($section);
			}
			foreach ($request->getElements($section) as $element){
				if(!$this->hasDefinition($section, $element)){
					$this->addRequestElement($section, $element);
				}
			}
		}
	}

	//////////////////
	//integrity checks
	//////////////////

	protected function assertSection($section){
		if(!$this->hasSection($section)){
			throw new Exception('section not found');
		}
	}

	protected function assertElement($section, $elementId){
		$this->assertSection($section);
		if(array_key_exists($elementId, $this->data[$section])){
			throw new Exception('element not found');
		}
	}

	protected function assertMissingSection($section){
		if($this->hasSection($section)){
			throw new Exception('section already exists');
		}
	}

}
?>
