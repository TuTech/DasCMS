<?php
abstract class _Search_Controller
	implements Search_Controller
{
	protected $request = null;
	protected $elements = array();

	protected function currentSection(){
		//remove the "Search_Controller_" prefix
		return substr(get_class($this), 18);
	}

	public function setRequest(Search_Request $request){
		$this->request = $request;
		$sect = $this->currentSection();
		if($request->hasSection($sect)){
			$this->elements = $request->getElements($sect);
		}
	}

	public function gather(){}
	public function filter(){}
	public function rate(){}

}
?>
