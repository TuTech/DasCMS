<?php
class Import_Version1_JSON implements Import_Version1
{
	protected $items = array();

	public function  __construct($jsonString) {
		$data = @json_decode($jsonString, true);
		if($data == null || !is_array($data)){
			throw new Exception('could not load data');
		}

		if(isset($data['importId']) 
				&& isset($data['title'])
				&& isset($data['type'])
		){
			//single content
			$this->items[] = new Import_Version1_JSONDocument($data);
		}
		else{
			//multiple contents
			foreach($data as $key => $doc){
				if(is_numeric($key)){
					$this->items[] = new Import_Version1_JSONDocument($doc);
				}
			}
		}
	}

	/**
	 * @return int
	 */
	public function getItemCount() {
		return count($this->items);
	}

	/**
	 * @param int $number
	 * @return Import_Version1_Document
	 */
	public function getItem($number) {
		if(!isset($this->items[$number])){
			throw new Exception('item not found');
		}
		return $this->items[$number];
	}
}
?>
