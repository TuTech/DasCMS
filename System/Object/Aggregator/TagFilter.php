<?php
class Aggregator_TagFilter extends _Aggregator
{
    const MATCH_ANY = 1;
    const MATCH_ALL = 2;
    const EXCLUDE_MATCH_ANY = 3;
    const EXCLUDE_MATCH_ALL = 4;
    
    protected $mode = 1;
    protected $tags = array();
    protected $attributes = array('mode','tags');
    
    public function getType()
    {
        return _Aggregator::PREDEFINED;
    }
    
    public function aggregate()
    {
        //aggregate all contents at once 
        //so... drop all first
        if(!$this->getAggregatorID())
        {
            throw new BadMethodCallException('this needs to be saved to be used');
        }
        //has unaggregated stuff?
        $res = QAggregatorTagFilter::countUnaggregatedContents($this->getAggregatorID());
        list($count) = $res->fetch();
        $res->free();
        
        if($count > 0)
        {
            $DB = DSQL::getSharedInstance();
            $DB->beginTransaction();
            QAggregatorTagFilter::removeAllContents($this->getAggregatorID());
            $aggregated = 0;
            switch ($this->mode)
            {
                case self::MATCH_ALL:
                case self::MATCH_ANY:
                    $aggregated = QAggregatorTagFilter::aggregateMatch($this->getAggregatorID(), $this->tags, self::MATCH_ALL == $this->mode);
                    break;
                case self::EXCLUDE_MATCH_ALL:
                case self::EXCLUDE_MATCH_ANY:
                    $aggregated = QAggregatorTagFilter::aggregateExcludeMatch($this->getAggregatorID(), $this->tags, self::EXCLUDE_MATCH_ALL == $this->mode);
                    break;
            }
            if($aggregated)
            {
                printf('%d to be aggregated and %d matched aggregator', $count, $aggregated);
                QAggregatorTagFilter::setAllContentsAggreagated($this->getAggregatorID());
            }
            //remove unaggregated stuff from list
            $DB->commit();
        }
    }
    
    public function getTags()
    {
        return $this->tags;
    }
    
    public function setTags(array $tags)
    {
        $this->tags = $tags;
    }
    
    public function getFilterMethod()
    {
        return $this->mode;
    }
    
    public function setFilterMethod($mode)
    {
        if(!($mode == 1 || $mode == 2 || $mode == 3 || $mode == 4))
        {
            throw new XUndefinedIndexException('invalid mode given');
        }
        $this->mode = $mode;
    }
}
?>