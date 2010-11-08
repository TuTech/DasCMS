<?php
abstract class _Import_Content
{
	protected function clearHistory($id){

	}

	protected function addActionToHistory($id, $date, $user){
		
	}

	protected function applyBasicAttributes(Interface_Content $content, Import_Version1_Document $data){
		$content->setSubTitle($data->getSubTitle());
		$content->setDescription($data->getDescription());
		$content->setPubDate($data->getPubDate());
		$content->setRevokeDate($data->getRevokeDate());
	}
}
?>
