<?php
interface Search_Controller{
	public function setRequest(Search_Request $request);
	public function setSearchId($id);
	public function gather(){}
	public function filter(){}
	public function rate(){}
}
?>