<?php
interface Import_Version1_References
{
	/**
	 * @return array
	 */
	public function getReferenceSections();

	/**
	 * @param string $section
	 * @return int
	 */
	public function getReferenceCountInSection($section);

	/**
	 * @param int $number
	 * @param string $section
	 * @return Import_Version1_Reference
	 */
	public function getReferenceInSection($number, $section);

}
?>
