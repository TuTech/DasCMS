<?php
interface Interface_Content_InfiniteScope
{
    public function getNumberOfContentsOnPage();
    public function getPageTitle();
    public function getNextPageTitle();
    public function getPreviousPageTitle();
    public function getNextPageLink();
    public function getPreviousPageLink();
    public function getLinkToPage($pageNo);
    public function getPageContents();
}
?>