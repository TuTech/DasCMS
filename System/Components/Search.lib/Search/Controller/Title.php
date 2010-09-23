<?php
class Search_Controller_Title
	extends _Search_Controller
	implements Search_Label_Title, Search_Label_Global
{
	protected function gatherValue($string){
		return '%'.$string.'%';
	}
}
?>
