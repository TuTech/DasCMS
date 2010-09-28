<?php
class Search_Controller_Description
	extends _Search_Controller
	implements Search_Label_Desc, Search_Label_Description, Search_Label_Global
{
	protected function gatherValue($string){
		return '%'.$string.'%';
	}

	protected function filterValue($string) {
		return $this->gatherValue($string);
	}
}
?>
