<?php
class Model_Search_RequestElement
{
	const MAY_HAVE = 0;
	const MUST_HAVE = 1;
	const MUST_NOT_HAVE = -1;

	static protected $elements = array();
	static protected $modMap = array(-1 => '-', 0 => '', 1 => '+');

	protected $value;
	protected $modifier;

	public static function create($value, $modifier){
		if($modifier != self::MAY_HAVE
				&& $modifier != self::MUST_HAVE
				&& $modifier != self::MUST_NOT_HAVE)
		{
			throw new Exception('wrong modifier');
		}
		$id = self::id($value, $modifier);
		if(!array_key_exists($id, self::$elements)){
			self::$elements[$id] = new Model_Search_RequestElement($value, $modifier);
		}
		return self::$elements[$id];
	}

	protected function  __construct($value, $modifier) {
		$this->modifier = $modifier;
		$this->value = $value;
	}

	protected static function id($value, $modifier){
		return sprintf('%s%s', self::$modMap[$modifier], $value);
	}

	public function getValue(){
		return $this->value;
	}

	public function getModifier(){
		return $this->modifier;
	}

	public function getId(){
		return self::id($this->value, $this->modifier);
	}

	public function  __toString() {
		return $this->getId();
	}
}
?>
