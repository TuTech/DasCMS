<?php
class Controller_SearchComponent_Subtitle
	extends _Controller_Search
	implements
		Label_Search_Subtitle,
		Label_Search_Global,
		Interface_Search_OrderingDelegate
{
	protected function gatherValue($string){
		return '%'.$string.'%';
	}

	protected function filterValue($string) {
		return $this->gatherValue($string);
	}

}
?>
