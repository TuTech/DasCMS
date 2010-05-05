<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2007-11-28
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage Drivers
 */
class DSQLResult_SQLite extends DSQLResult 
{
	/**
	 * @var SQLiteResult
	 */
	private $result;
	/**
	 * @var SQLiteDatabase
	 */
	private $database;
	private $row = 0;
	
	public function __construct(SQLiteDatabase $database, SQLiteResult $result)
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
		return $this->result->fetch();
	}
	
	/**
	 * fetch all fields of this row as an object and focus next row
	 * @return object
	 */
	public function fetchObject()
	{
		$this->row++;
		return $this->result->fetchObject();
	}
	
	/**
	 * get the number of fetched rows
	 * @return int
	 */
	public function getRowCount()
	{
		return $this->result->numRows();
	}
	
	/**
	 * get the number of fields 
	 * @return int
	 */
	public function getFieldCount()
	{
		return $this->result->numFields();
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
		return $this->result->fieldName($no);
	}
	
	/**
	 * go to row number (0..row_count-1)
	 * @param int $to
	 * @return boolean
	 */
	public function seekRow($to)
	{
		$succ = $this->result->seek($to);
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
		return $this->row < $this->result->numRows();
	}
}
?>