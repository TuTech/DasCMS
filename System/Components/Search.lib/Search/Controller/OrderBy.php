<?php
class Search_Controller_OrderBy
	extends _Search_Controller
	implements Search_Label_Orderby
{
	public function setRequest(Search_Request $request) {
		parent::setRequest($request);
		$resolver = Search_LabelResolver::getInstance();
		//FIXME score by all
		$resolved = array();
		$delegate = null;
		//paginate after score
		foreach($this->keywords as $ctrl){
			$resolved = array_merge($resolved, $resolver->getControllersForLabel($ctrl));
		}
		$resolved = array_unique($resolved);
		foreach ($resolved as $controller){
			$class = $resolver->controllerToClass($controller);
			if(Core::isImplementation($class, 'Search_Interface_OrderingDelegate')){
				$delegate = $class;
			}
		}
		if($delegate){
			$delegate = new $delegate;
			if($delegate instanceof Search_Interface_OrderingDelegate){
				$delegate->setSearchId($this->searchId);
				$delegate->setRequest($request);
				Search_Engine::getInstance()->setOrderingDelegate($delegate);
			}
		}
	}
}
?>
