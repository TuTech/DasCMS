<?php
class Model_Content_Composite_ContentAggregator extends _Model_Content_Composite
{
    private $aggregatorName = null;
    private $aggregator = null;
    
    public static function getCompositeMethods()
    {
        return array(
        	'getContentAggregatorInstance', 
        	'getContentAggregator', 
        	'setContentAggregator',
        );
    }
    
    public function __construct(Interface_Content $compositeFor)
    {
        parent::__construct($compositeFor);
    }

    public function setContentAggregator($aggregatorName)
	{
	    QContentAggregator::setAggregator($this->compositeFor->getId(), $aggregatorName);
	} 
	
	public function getContentAggregator()
	{
	    if($this->aggregatorName === null)
	    {
	        $this->aggregatorName = false;
	        $res = QContentAggregator::getAggregatorName($this->compositeFor->getId()); 
    	    if($res->getRowCount() == 1)
    	    {
    	        list($this->aggregatorName) = $res->fetch(); 
    	    }
    	    $res->free();
	    }
	    return $this->aggregatorName;
	}
	
	public function getContentAggregatorInstance()
	{
	    if($this->aggregator === null)
	    {
	        $this->aggregator = false;
	        if($this->getContentAggregator() != false)
	        {
	            $this->aggregator = Controller_Aggregators::getSharedInstance()->getSavedAggregator($this->getContentAggregator());
	        }
	    }
	    return $this->aggregator;
	}
} 
?>