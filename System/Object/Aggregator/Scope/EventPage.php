<?php
class Aggregator_Scope_EventPage 
    extends Aggregator_Scope_Page
    implements Interface_Content_FiniteScope
{
    /**
     * @param Interface_Content $source
     * @param int $itemsPerPage
     * @param int $pageNo 1-based index
     */
    public function __construct(_Aggregator $source, Interface_Content $host, $itemsPerPage, $pageNo = 1)
    {
        if(!$host->implementsInterface('Interface_Content_ScopeCallback'))
        {
            throw new XArgumentException('content has no scope callback');
        }
        parent::__construct($source, $host, $itemsPerPage, $pageNo);
    }
    
    /**
     * @return int
     */
    public function getNumberOfContents()
    {
        if($this->contentCount === null)
        {
            $res = QAggregatorScopeEventPage::countItems(
                $this->source->getAggregatorTable(), 
                $this->source->getAggregatorID()
            );
            list($this->contentCount) = $res->fetch();
            $res->free();
        }
        return $this->contentCount;
    }
    
    /**
     * @return array
     */
    public function getPageContents()
    {
        if($this->pageContents === null)
        {
            $this->pageContents = array();
            $res = QAggregatorScopeEventPage::fetchItems(
                $this->source->getAggregatorTable(), 
                $this->source->getAggregatorID(),
                max($this->pageNo-1,0)*$this->itemsPerPage,//offset
                $this->itemsPerPage,//LIMIT
                'table.field for order',
                'order direction',
                'table to join for ordering'//QAggregatorScopePage has a join map (null if not necessary
            );
            while ($row = $res->fetch())
            {
                $this->pageContents[] = $row;
            }
            $res->free();
        }
        return $this->pageContents;
    }
}
?>