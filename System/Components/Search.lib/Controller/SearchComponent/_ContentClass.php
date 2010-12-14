<?php
abstract class _Controller_SearchComponent_ContentClass
	extends _Controller_Search
{
	/**
	 * store class name lookups
	 * @var array
	 */
	protected static $lookup = array();

	/**
	 * resolve class name to class id
	 * @param string $class
	 * @return int
	 */
	protected function lookup($class){
		$c = strtolower($class);
		if(!isset (self::$lookup[$c])){
			self::$lookup[$c] = Core::Database()
				->createQueryForClass($this->classForQuery())
				->call('lookup')
				->withParameters($c)
				->fetchSingleValue();
		}
		return self::$lookup[$c];
	}

	protected function classForQuery() {
		return '_Controller_SearchComponent_ContentClass';
	}

	protected function gatherValue($string){
		return $this->lookup($string);
	}

	protected function filterValue($string) {
		return $this->lookup($string);
	}
}
?>
