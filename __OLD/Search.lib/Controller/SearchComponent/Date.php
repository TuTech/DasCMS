<?php
class Controller_SearchComponent_Date
	extends _Controller_Search
	implements
		Label_Search_Date,
		Label_Search_Pubdate,
		Interface_Search_OrderingDelegate
{

	//no influence in gather/filter/score, just to order by date

	public function gather() {}
	public function filter() {}
	public function rate() {}
}
?>
