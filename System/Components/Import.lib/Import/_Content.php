<?php
abstract class _Import_Content
{
	/**
	 * @param Interface_Content $content
	 * @param Import_Version1_Document $data
	 */
	protected function alterHistory(Interface_Content $content, Import_Version1_Document $data){
		$id = $content->getId();
		$cdate = $data->getCreateDate();
		$mdate = $data->getModifyDate();
		if(!empty ($cdate) || !empty ($mdate)){
			$this->clearHistory($id);
		}
		if(!empty ($cdate)){
			$this->addActionToHistory($id, $cdate, $data->getCreator(), $data->getTitle(), empty ($mdate));
		}
		if(!empty ($mdate)){
			$this->addActionToHistory($id, $mdate, $data->getModifier(), $data->getTitle(), true);
		}
	}

	/**
	 * @param Import_Version1_Document $data
	 * @return string
	 */
	protected function getContentDecoded(Import_Version1_Document $data){
		switch ($data->getContentEncoding()){
			case 'base64':
				return base64_decode($data->getContent());
			case 'none':
			default:
				return $data->getContent();
		}
	}

	/**
	 * @param int $id
	 */
	protected function clearHistory($id){
		Core::Database()
			->createQueryForClass('_Import_Content')
			->call('clear')
			->withParameters($id)
			->execute();
	}

	/**
	 * add an entry to the content history
	 * @param int $id
	 * @param int $date
	 * @param string $user
	 * @param string $title
	 * @param bool $latest
	 */
	protected function addActionToHistory($id, $date, $user, $title, $latest = false){
		$Db = Core::Database()->createQueryForClass('_Import_Content');
		$uid = $Db->call('logUID')
			->withParameters($user)
			->fetchSingleValue();
		if($uid == null){
			$uid = $Db->call('addLogUser')
				->withParameters($user)
				->executeInsert();
		}
		$Db->call('add')//contentREL, changeDate, title, size, userREL, latest
			->withParameters($id, date('Y-m-d H:i:s', $date), $title, 0, $uid, $latest ? 'Y' : 'N')
			->executeInsert();
	}

	/**
	 * update default attributes
	 * @param Interface_Content $content
	 * @param Import_Version1_Document $data
	 */
	protected function applyBasicAttributes(Interface_Content $content, Import_Version1_Document $data){
		$content->setSubTitle($data->getSubTitle());
		$content->setDescription($data->getDescription());
		$content->setPubDate($data->getPubDate());
		$content->setRevokeDate($data->getRevokeDate());
		$content->setTags($data->getTags());
	}
}
?>
