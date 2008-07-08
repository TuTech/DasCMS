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
	
	public function __construct(mysqli $database, mysqli_result $result)
	{
		$this->database = $database;
		$this->result = $result;
	}
	
	/**
	 * fetch all fields of this row as an array and focus next row
	 * @return array
	 */
	public function fetch()
	{
		$this->row++;
		return $this->result->fetch_array();
	}
	
	/**
	 * fetch all fields of this row as an object and focus next row
	 * @return object
	 */
	public function fetchObject()
	{
		$this->row++;
		return $this->result->fetch_object();
	}

	/**
	 * get the number of fetched rows
	 * @return int
	 */
	public function getRowCount()
	{
		return $this->result->num_rows;
	}
	
	/**
	 * get the number of fields 
	 * @return int
	 */
	public function getFieldCount()
	{
		return $this->result->field_count;
	}
	
	/**
	 * get the current row number
	 * @return int
	 */
	public function getCurrentRow()
	{
		return $this->row;
	}
	
	/**
	 * get the name of the field at $no
	 * @param int $no
	 * @return string
	 */
	public function fieldName($no)
	{
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
		return $this->row < $this->result->num_rows;
	}
	
	public function __destruct()
	{
		$this->result->free();
	}
}
?>