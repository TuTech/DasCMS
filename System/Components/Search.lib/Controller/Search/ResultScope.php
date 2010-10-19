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
	private $view;

	/**
	 * @param Model_Search_Result $result
	 * @param Controller_View_Content $spore
	 */
	public function __construct(Interface_Search_ResultPage $result, Controller_View_Content $spore) {

	}

	public function getNextPageLink() {
	 ;
	}

	public function getNextPageTitle() {
	 ;
	}

	public function getNumberOfAvailablePages() {
	 ;
	}

	public function getNumberOfContents() {
	 ;
	}

	public function getNumberOfContentsOnPage() {
	 ;
	}

	public function getNumberOfCurrentPage() {
	 ;
	}

	public function getPageContents() {
	 ;
	}

	public function getPageTitle() {
	 ;
	}

	public function getPreviousPageLink() {
	 ;
	}

	public function getPreviousPageTitle() {
	 ;
	}

	public function isFirstPage() {
	 ;
	}

	public function isLastPage() {
	}
}
?>
