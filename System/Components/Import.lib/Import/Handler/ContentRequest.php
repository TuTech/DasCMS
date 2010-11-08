<?php
interface Import_Handler_ContentRequest
{
	public function setRequest(Import_Request_Content $content);

	/**
	 * @return Interface_Content
	 */
	public function createContentWithData(Import_Version1_Document $data);
}
?>
