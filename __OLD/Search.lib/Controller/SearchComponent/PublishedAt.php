<?php
class Controller_SearchComponent_PublishedAt
	extends _Controller_Search
	implements Label_Search_Pubdate
{
	protected function parseRequest() {
		//parse date input
		if(count($this->elements) > 0){
			$time = strtotime($this->elements[0]->getValue());
			if($time){
				$this->keywords[] = $time;
				$this->required[] = $time;
			}
		}
	}
}
?>
