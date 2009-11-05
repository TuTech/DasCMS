<?php
class Aggregator_Scope_Page 
    extends _Aggregator_Scope
    implements Interface_Content_FiniteScope
{
    /**
     * @var _Aggregator
     */
    protected $source;
    protected $itemsPerPage;
    protected $pageNo;
    
    protected $contentCount = null;
    protected $pageContents = null;

    /**
     * @var Interface_Content_ScopeCallback
     */
    protected $scopeCallback = null;
    /**
     * @param Interface_Content $source
     * @param int $itemsPerPage
     * @param int $pageNo 1-based index
     */
    public function __construct(_Aggregator $source, Interface_Content $host, $itemsPerPage)
    {
        if(!$host->implementsInterface('Interface_Content_ScopeCallback'))
        {
            throw new XArgumentException('content has no scope callback');
        }
        $this->source = $source;
        $this->itemsPerPage = $itemsPerPage;
        $data = $host->getScopeData();
        $pageNo = (isset($data['page'])) ? $data['page'] : 1;
        $this->pageNo = min(max(1,intval($pageNo)), $this->getNumberOfAvailablePages());
        $this->scopeCallback = $host;
    }
    
    public function getNumberOfCurrentPage()
    {
        return $this->pageNo;
    }
    
    /**
     * @return int
     */
    public function getNumberOfContents()
    {
        if($this->contentCount === null)
        {
            $res = QAggregatorScopePage::countItems(
                $this->source->getAggregatorTable(), 
                $this->source->getAggregatorID()
            );
            list($this->contentCount) = $res->fetch();
            $res->free();
        }
        return $this->contentCount;
    }
    
    /**
     * @return int
     */
    public function getNumberOfContentsOnPage()
    {
        return count($this->getPageContents());
    }
    
    /**
     * @return int
     */
    public function getPageTitle()
    {
        return $this->pageNo;
    }  
      
   /**
     * @return string
     */
    public function getNextPageTitle()
    {
        return $this->pageNo+1;
    }  
      
    /**
     * @return string
     */
    public function getPreviousPageTitle()
    {
        return $this->pageNo-1;
    }  
    
   /**
     * @return string
     */
    public function getNextPageLink()
    {
        return ($this->pageNo == $this->getNumberOfAvailablePages())  
            ? null
            : $this->scopeCallback->getLinkWithScopeData(array('page' => $this->pageNo+1));
    }  
      
    /**
     * @return string
     */
    public function getPreviousPageLink()
    {
        return ($this->pageNo > 1)
            ? $this->scopeCallback->getLinkWithScopeData(array('page' => $this->pageNo - 1))
            : null;
    }  
    
    
    /**
     * @return bool
     */
    public function isFirstPage()
    {
        return $this->pageNo == 1;
    }
     
    /**
     * @return bool
     */
    public function isLastPage()
    {
        return $this->pageNo == $this->getNumberOfAvailablePages();
    }
    
    /**
     * @return int
     */
    public function getNumberOfAvailablePages()
    {
        return ceil($this->getNumberOfContents()/$this->itemsPerPage);
    }

    public function hasInfinitePages()
    {
        return false;
    }
    
    /**
     * @return array
     */
    public function getPageContents()
    {
        if($this->pageContents === null)
        {
            $this->pageContents = array();
            $res = QAggregatorScopePage::fetchItems(
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
                $this->pageContents[] = $row[0];
            }
            $res->free();
        }
        return $this->pageContents;
    }
}
?>