<?php
class Aggregator_Scope_EventPage 
    extends Aggregator_Scope_Page
    implements Interface_Content_FiniteScope
{
    /**
     * @param BContent $source
     * @param int $itemsPerPage
     * @param int $pageNo 1-based index
     */
    public function __construct(_Aggregator $source, Interface_Content_ScopeCallback $host, $itemsPerPage, $pageNo = 1)
    {
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
                ($this->pageNo-1)*$this->itemsPerPage,//offset
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