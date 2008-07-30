<?php
class DSQLResult_MySQL extends DSQLResult 
{
	/**
	 * @var mysqli_result
	 */
	private $result;
	/**
	 * @var mysqli
	 */
	private $database;
	private $row = 0;
	private $ptok;
	
	public function __construct(mysqli $database, mysqli_result $result)
	{
		$this->database = $database;
		$this->result = $result;
		$this->ptok = SProfiler::profile(__FILE__, __LINE__, "MySQL result fetch ".$result->num_rows." rows");
	}
	
	/**
	 * fetch all fields of this row as an array and focus next row
	 * @return array
	 */
	public function fetch()
	{
		$this->checkResult();
		$this->row++;
		return $this->result->fetch_array();
	}
	
	/**
	 * fetch all fields of this row as an object and focus next row
	 * @return object
	 */
	public function fetchObject()
	{
		$this->checkResult();
		$this->row++;
		return $this->result->fetch_object();
	}

	/**
	 * get the number of fetched rows
	 * @return int
	 */
	public function getRowCount()
	{
		$this->checkResult();
		return $this->result->num_rows;
	}
	
	/**
	 * get the number of fields 
	 * @return int
	 */
	public function getFieldCount()
	{
		$this->checkResult();
		return $this->result->field_count;
	}
	
	/**
	 * get the current row number
	 * @return int
	 */
	public function getCurrentRow()
	{
		$this->checkResult();
		return $this->row;
	}
	
	/**
	 * get the name of the field at $no
	 * @param int $no
	 * @return string
	 */
	public function fieldName($no)
	{
		$this->checkResult();
		$meta = $this->result->fetch_field_direct($no);
		return $meta['name'];
	}
	
	/**
	 * go to row number (0..row_count-1)
	 * @param int $to
	 * @return boolean
	 */
	public function seekRow($to)
	{
		$this->checkResult();
		$succ = $this->result->data_seek($to);
		if($succ)
		{
			$this->row = $to;
		}
		return $succ;
	}
	
	/**
	 * are there unfetched rows 
	 *
	 * @return boolean
	 */
	public function hasNext()
	{
		$this->checkResult();
		return $this->row < $this->result->num_rows;
	}
	
	private function checkResult()
	{
		if($this->result === null)
		{
			throw new XDatabaseException('you can not use a result after calling free()');
		}
	}
	
	public function free()
	{
		if($this->result != null && is_object($this->result))
		{
			$this->result->free();
			$this->result = null;
		}
	}
	
	public function __destruct()
	{
		if($this->result != null && is_object($this->result))
		{
			$this->result->free();
		}
		SProfiler::finish($this->ptok);
	}
}
?>