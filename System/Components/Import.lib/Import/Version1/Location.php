<?php
interface Import_Version1_Location
{
	/**
	 * @return string
	 */
	public function getAddress();

	/**
	 * @return string
	 */
	public function getLongitude();

	/**
	 * @return string
	 */
	public function getLatitude();

	/**
	 * @return string
	 */
	public function getLocationName();

	/**
	 * @return bool
	 */
	public function hasData();

}
?>
