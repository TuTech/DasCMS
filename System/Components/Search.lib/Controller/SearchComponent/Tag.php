<?php
class Controller_SearchComponent_Tag
	extends _Controller_Search
	implements 
		Label_Search_Tag
{
	protected function gatherValue($string){
		return str_replace('*', '%', $string);
	}

	protected function filterValue($string){
		return $this->gatherValue($string);
	}
}
?>
