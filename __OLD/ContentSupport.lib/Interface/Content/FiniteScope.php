<?php
interface Interface_Content_FiniteScope 
    extends Interface_Content_InfiniteScope
{
    public function getNumberOfContents();
    public function isFirstPage();
    public function isLastPage();
    public function getNumberOfAvailablePages();
    public function getNumberOfCurrentPage();
}
?>