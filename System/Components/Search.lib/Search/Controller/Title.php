<?php
class Search_Controller_Title
	extends _Search_Controller
	implements
		Search_Label_Title,
		Search_Label_Global,
		Search_Interface_OrderingDelegate
{
	protected function gatherValue($string){
		return '%'.$string.'%';
	}

	protected function filterValue($string) {
		return $this->gatherValue($string);
	}
}
?>
