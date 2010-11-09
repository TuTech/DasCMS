<?php
class Import_Content_CPage extends _Import_Content implements Import_Handler_ContentRequest
{
	public function setRequest(Import_Request_Content $content) {
		$content->registerForMimetypes(array('text', 'text/html', 'text/xhtml'), $this);
	}

	public function createContentWithData(Import_Version1_Document $data) {
		//create content and set data
		$c = CPage::Create($data->getTitle());
		$this->applyBasicAttributes($c, $data);
		$c->setContent($this->getContentDecoded($data));
		$c->save();

		//change history of this content
		$this->alterHistory($c, $data);
	}
}
?>
