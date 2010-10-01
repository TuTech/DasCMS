<?php
class Search_Controller_Title
	extends _Search_Controller
	implements
		Search_Label_Date,
		Search_Label_Pubdate,
		Search_Interface_OrderingDelegate
{

	//no influence in gather/filter/score, just to order by date

	public function gather() {}
	public function filter() {}
	public function rate() {}
}
?>
