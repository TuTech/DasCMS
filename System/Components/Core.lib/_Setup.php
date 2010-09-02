<?php
abstract class _Setup
{
	protected $contentFolder;
	protected $input;
	protected $report = array();


	protected function setupInDatabase($firstStatement){
		$statementNames = func_get_args();
		foreach ($statementNames as $statement){
			Core::Database()
				->createQueryForClass($this)
				->call($statement)
				->withoutParameters()
				->execute();
		}
	}

	protected function inputValueForKey($key, $default = ''){
		$val = $default;
		if(array_key_exists($key, $this->input)){
			$val = $this->input[$key];
		}
		return $val;
	}


	protected function reportError($forKey, $withMessage){
		$this->report[] = array(get_class($this), $forKey, $withMessage);
	}

	protected function getReport(){
		return $this->report;
	}


	protected function dirPath($dir){
		return $this->contentFolder.'/'.$dir;
	}

	protected function setupDir($folder){
		$folder = $this->dirPath($folder);
		return mkdir($folder) && is_readable($folder) && is_writable($folder);
	}

	public function setContentFolder($folder) {
		$this->contentFolder = $folder;
	}

	public function setInputData(array $data) {
		$this->input = $data;
	}

	public function validateInputData() {
		return array();
	}


}
?>
