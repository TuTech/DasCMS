<?php
class Search_Controller_OrderBy
	extends _Search_Controller
	implements Search_Label_OrderBy
{
	public function setRequest(Search_Request $request) {
		parent::setRequest($request);
		//FIXME score by all
		//paginate after score
		$controller = $this->keywords[0];


		//resolve ordering controller
		//if controller can order
			Search_Engine::getInstance()->setOrderingDelegate($this);
	}
}
?>
