<?php
abstract class _Search_Controller_SimpleTable
	extends _Search_Controller
{
	protected function gatherValue($string){
		return $string;
	}

	public function gather() {
		$dba = Core::Database()
				->createQueryForClass($this)
				->call('gather');
		foreach ($this->keywords as $criteria){
			$criteria = $this->gatherValue($criteria);
			if($criteria){
				$dba->withParameters($this->searchId, $criteria)->executeInsert();
			}
		}
	}
}
?>
