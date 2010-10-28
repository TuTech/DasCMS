<?php
/**
 * search in word index created by an indexer class
 *
 * search index:
 * default gatherer: concat values tag, title, subtitle, description, text content
 */
class Controller_SearchComponent_Default
	extends _Controller_Search
	implements
		Label_Search_Global
{
	protected function gatherValue($string){
		return '%'.$string.'%';
	}

	protected function filterValue($string) {
		return $this->gatherValue($string);
	}

	public function gather() {
		if(!Core::Database()->hasQueryForClass('gather', $this)){
			return;
		}
		$dba = Core::Database()
				->createQueryForClass($this)
				->call('gather');
		foreach ($this->keywords as $criteria){
			$criteria = $this->gatherValue($criteria);
			if($criteria){
				$dba->withParameters($this->searchId, $criteria, $criteria, $criteria)->executeInsert();
			}
		}
	}

	public function filter(){
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
					$criteria = $this->filterValue($criteria);
					if($criteria){
						$dba->withParameters($this->searchId, $criteria, $criteria, $criteria)->execute();
					}
				}
			}
		}
	}
}
?>