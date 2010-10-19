<?php
class Controller_SearchComponent_OrderBy
	extends _Controller_Search
	implements Label_Search_Orderby
{
	public function setRequest(Model_Search_Request $request) {
		parent::setRequest($request);
		$resolver = Controller_Search_LabelResolver::getInstance();
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
			if(Core::isImplementation($class, 'Interface_Search_OrderingDelegate')){
				$delegate = $class;
			}
		}
		if($delegate){
			$delegate = new $delegate;
			if($delegate instanceof Interface_Search_OrderingDelegate){
				$delegate->setSearchId($this->searchId);
				$delegate->setRequest($request);
				Controller_Search_Engine::getInstance()->setOrderingDelegate($delegate);
			}
		}
	}
}
?>
