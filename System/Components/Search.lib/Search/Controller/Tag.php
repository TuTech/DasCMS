<?php
class Search_Controller_Tag
	extends _Search_Controller
	implements Search_Label_Tag, Search_Label_Global
{
	public function filter(){


		return;
	 
		//resolve tag
		
		//get content ids of contents with tag
	 
		//remove contents [not] in list


		$rules = array(
			'Require' => &$this->required,
			'Veto'    => &$this->vetoed
		);
		$db = Core::Database();
		$dba = $db->createQueryForClass($this);
		foreach ($rules as $filter => $elements){
			$query = 'filter'.$filter;
			if($db->hasQueryForClass($query, $this)){
				$dba = $dba->call($query);
				foreach ($elements as $criteria){
					$dba->withParameters($this->searchId, $criteria)->execute();
				}
			}
		}
	}
}
?>
