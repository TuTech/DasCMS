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
abstract class DSQLResult
{
	/**
	 * fetch all fields of this row as an array and focus next row
	 * @return array
	 */
	abstract public function fetch();
	
	/**
	 * fetch all fields of this row as an object and focus next row
	 * @return object
	 */
	abstract public function fetchObject();

	/**
	 * get the number of fetched rows
	 * @return int
	 */
	abstract public function getRowCount();
	
	/**
	 * get the number of fields 
	 * @return int
	 */
	abstract public function getFieldCount();
	
	/**
	 * get the current row number
	 * @return int
	 */
	abstract public function getCurrentRow();
	
	/**
	 * get the name of the field at $no
	 * @param int $no
	 * @return string
	 */
	abstract public function fieldName($no);
	
	/**
	 * go to row number (0..row_count-1)
	 * @param int $to
	 * @return boolean
	 */
	abstract public function seekRow($to);
	
	/**
	 * are there unfetched rows 
	 *
	 * @return boolean
	 */
	abstract public function hasNext();
		
	/**
	 * frees result after use
	 */
	public function free(){}
}
?>