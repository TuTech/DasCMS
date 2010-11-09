<?php
class Import_Content_CLink extends _Import_Content implements Import_Handler_ContentRequest
{
	public function setRequest(Import_Request_Content $content) {
		$content->registerForMimetypes(array('url', 'href', 'text/url', 'text/href'), $this);
	}

	public function createContentWithData(Import_Version1_Document $data) {
		//create content and set data
		$c = CLink::Create($data->getTitle());
		$this->applyBasicAttributes($c, $data);
		$c->setContent($this->getContentDecoded($data));
		$c->save();

		//change history of this content
		$this->alterHistory($c, $data);
	}
}
?>
