<?php
class Import_Content_HTML extends _Import_Content implements Import_Handler_ContentRequest
{
	public function setRequest(Import_Request_Content $content) {
		$content->registerForMimetypes(array('text', 'text/html', 'text/xhtml'), $this);
	}

	public function createContentWithData(Import_Version1_Document $data) {
		//create content and set data
		$c = CPage::Create($data->getTitle());
		$this->applyBasicAttributes($c, $data);
		$c->setContent($data->getContent());
		$c->save();

		//change history of this content
		$id = $c->getId();
		$this->clearHistory($id);
		$this->addActionToHistory($id, $data->getCreateDate(), $data->getCreator());
		$this->addActionToHistory($id, $data->getModifyDate(), $data->getModifier());
	}
}
?>
