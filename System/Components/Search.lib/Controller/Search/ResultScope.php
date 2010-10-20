<?php
class Controller_Search_ResultScope implements Interface_Content_FiniteScope
{
	/**
	 * @var Interface_Search_ResultPage
	 */
	private $result;

	/**
	 * @var Controller_View_Content
	 */
	private $contentView;

	private $nextTitle = '&gt;&gt;',
			$prevTitle = '&lt;&lt;';


	/**
	 * @param Interface_Search_ResultPage $result
	 * @param Controller_View_Content $spore
	 */
	public function __construct(Interface_Search_ResultPage $result, Controller_View_Content $contentView) {
		$this->result = $result;
		$this->contentView = $contentView;
	}

	public function setNextTitle($nextTitle){
		$this->nextTitle = $nextTitle;
	}

	public function setPrevTitle($prevTitle){
		$this->prevTitle = $prevTitle;
	}

	public function getNextPageLink() {
		return $this->contentView->SetLinkParameter('page', min(
				$this->result->getPageNumber() + 1,
				$this->result->getLastPageNumber()
			), true);
	}

	public function getNextPageTitle() {
		return $this->nextTitle;
	}

	public function getPageTitle() {
		return $this->contentView->getContent()->getTitle();
	}

	public function getPreviousPageLink() {
		return $this->contentView->SetLinkParameter('page', max(1, $this->result->getPageNumber() - 1), true);
	}

	public function getPreviousPageTitle() {
		return $this->prevTitle;
	}

	public function getNumberOfAvailablePages() {
		return $this->result->getLastPageNumber();
	}

	public function getNumberOfContents() {
		return $this->result->getTotalElementCount();
	}

	public function getNumberOfContentsOnPage() {
		return $this->result->getPageElementCount();
	}

	public function getNumberOfCurrentPage() {
		return $this->result->getCurrentElementCount();
	}

	public function getPageContents() {
		$this->contentView->getContent();
	}

	public function isFirstPage() {
		return $this->result->isFirstPage();
	}

	public function isLastPage() {
		return $this->result->isLastPage();
	}
}
?>
