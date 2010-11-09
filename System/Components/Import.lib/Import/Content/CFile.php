<?php
class Import_Content_CFile extends _Import_Content implements Import_Handler_ContentRequest
{
	public function setRequest(Import_Request_Content $content) {
		$content->registerForMimetypes(array('*'), $this);
	}

	public function createContentWithData(Import_Version1_Document $data) {
		//create content and set data
		$tmp = tempnam(constant('CMS_TEMP'), 'Import_');
		file_put_contents($tmp, $this->getContentDecoded($data));
		$c = CFile::CreateWithFile($data->getTitle(), $tmp, $data->getType());
		$this->applyBasicAttributes($c, $data);
		$c->save();

		//change history of this content
		$this->alterHistory($c, $data);
	}
}
?>
