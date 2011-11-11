<?php
interface Setup_Component{
	public function setContentFolder($folder);
	public function setInputData(array $data);

	/**
	 * @return array [n => [class, input-key, message]]
	 */
	public function validateInputData();
}
?>
