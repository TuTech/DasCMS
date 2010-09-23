<?php
class Search_Controller_PublishedAt
	extends _Search_Controller
	implements Search_Label_Pubdate
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
