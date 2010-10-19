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

	/**
	 * @param Interface_Search_ResultPage $result
	 * @param Controller_View_Content $spore
	 */
	public function __construct(Interface_Search_ResultPage $result, Controller_View_Content $contentView) {
		$this->result = $result;
		$this->contentView = $contentView;
	}

	public function getNextPageLink() {
		return min(
				$this->result->getPageNumber() + 1,
				$this->result->getLastPageNumber()
			);
	}

	public function getNextPageTitle() {
		return $this->contentView->getContent()->getTitle();
	}

	public function getPageTitle() {
		return $this->contentView->getContent()->getTitle();
	}

	public function getPreviousPageLink() {
		return max(
				1,
				$this->result->getPageNumber() - 1
			);
	}

	public function getPreviousPageTitle() {
		return $this->contentView->getContent()->getTitle();
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
