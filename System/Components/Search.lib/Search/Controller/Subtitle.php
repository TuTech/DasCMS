<?php
class Search_Controller_Subtitle
	extends _Search_Controller
	implements
		Search_Label_Subtitle,
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
