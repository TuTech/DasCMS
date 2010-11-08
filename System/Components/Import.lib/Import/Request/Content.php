<?php
class Import_Request_Content
{
	protected $handler = null;
	protected $handlerScore = 0;
	protected $mimetype;


	protected function getMimetypeScore(array $mimetypes){
		//get data to compare to
		$cmp = $this->mimetype;
		$cmpcount = count($cmp);
		if($cmpcount == 0){
			//wildcard (no) mimetype
			return 1;
		}
		//compensate for doube increase for matching first part
		$cmpcount++;
		$maxScore = 0;
		//loop given mimetypes
		foreach ($mimetypes as $mt){
			$score = 0;
			//registered for all - only applies to contents with no better option
			if($mt == '*'){
				$score = '0.1';
			}
			//split mimetypes
			$mtsep = explode('/', $mt);
			//compare mimetype sections to each other and increase current score if they match
			for($i = 0; $i < min(count($mtsep), $cmpcount); $i++){
				$score += ($mtsep[$i] == $cmp[$i]);
				//rate first part higher
				if($i == 0 && $score == 1){
					$score++;
				}
			}
			//update max
			$maxScore = max($maxScore, $score);
		}
		//return score for the best intersection
		return $maxScore/$cmpcount;
	}


	public function __construct($mimetype) {
		$this->mimetype = explode('/', $mimetype);
		//resolve interface
		$classes = Core::getClassesWithInterface('Import_Handler_ContentRequest');
		foreach ($classes as $class){
			$object = new $class();
			if($object instanceof Import_Handler_ContentRequest){
				$object->setRequest($this);
			}
		}
	}

	public function registerForMimetypes(array $types, $object){
		$score = $this->getMimetypeScore($types);
		if($score > $this->handlerScore){
			$this->handlerScore = $score;
			$this->handler = $object;
		}
	}

	/**
	 * @return bool
	 */
	public function hasFoundHandler(){
		return $this->handler !== null;
	}

	/**
	 * @return Import_Handler_ContentRequest
	 */
	public function getImporter(){
		return $this->handler;
	}
}
?>
