<?php
class Controller_SearchComponent_Description
	extends _Controller_Search
	implements
		Label_Search_Desc,
		Label_Search_Description,
		Label_Search_Global
{
	protected function gatherValue($string){
		return '%'.$string.'%';
	}

	protected function filterValue($string) {
		return $this->gatherValue($string);
	}
}
?>
