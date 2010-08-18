<?php
interface Interface_Database_FetchableQuery{
	/**
	 * gets exactly one value from the query and closes ste query
	 * @return mixed
	 */
	 public function fetchSingleValue();

	 /**
	  * fetches the next line in the result array
	  * use this if you have provided a custom array via useResultArray()
	  * @return void
	  */
	 public function fetch();

	 /**
	  * fetches the next line in the result array and returns it
	  * @return array
	  */
	 public function fetchResult();

	 /**
	  * returns the lines affected by this query
	  * @return int
	  */
	 public function getAffectedRows();

	 /**
	  * returns the last insert id generated by this query
	  * @return int
	  */
	 public function getInsertID();

	 /**
	  * run non-query and clean up
	  */
	 public function execute();

	 /**
	  * clean up after fetch
	  */
	 public function close();
}

?>
