<?php
class Controller_SearchComponent_Description
	extends _Controller_Search
	implements
		Label_Search_Desc,
		Label_Search_Description
{
	protected function gatherValue($string){
		return '%'.$string.'%';
	}

	protected function filterValue($string) {
		return $this->gatherValue($string);
	}
}
?>
