<?php
class Search_Controller_Description
	extends _Search_Controller_SimpleTable
	implements Search_Label_Desc, Search_Label_Description, Search_Label_Global
{
	protected function gatherValue($string){
		return '%'.$string.'%';
	}
}
?>
