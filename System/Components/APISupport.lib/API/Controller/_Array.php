<?php
abstract class _API_Controller_Array implements API_Interface_AcceptsGet
{
	/**
	 * keys for the vallues provided by "$elements"
	 * @return array
	 */
	abstract protected function getKeys();//map: [guid, title]
	
	/**
	 * values to be mapped: [['id-value', 'title-value']]
	 * @return array|Interface_Database_FetchableQuery
	 */
	abstract protected function getElements();

	public function httpGet($queryString) {
		$out = array();
		$keys = $this->getKeys();
		$elements = $this->getElements();

		//array elements
		if(is_array($elements)){
			for($i = 0; $i < count($elements); $i++){
				$out[] = array_combine($keys, $elements[$i]);
			}
		}

		//database elements
		elseif($elements instanceof Interface_Database_FetchableQuery){
			while($row = $elements->fetchResult()){
				$out[] = array_combine($keys, $row);
			}
			$elements->free();
		}
		return $out;
	}
}
?>