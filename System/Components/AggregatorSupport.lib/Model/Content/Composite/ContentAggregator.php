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
		DSQL::getSharedInstance()->beginTransaction();
		Core::Database()
			->createQueryForClass($this)
			->call('removeAggregator')
			->withoutParameters($this->compositeFor->getId())
			->execute();
		Core::Database()
			->createQueryForClass($this)
			->call('setAggregator')
			->withParameters($this->compositeFor->getId(), $aggregatorName)
			->execute();
		DSQL::getSharedInstance()->commit();
	} 
	
	public function getContentAggregator()
	{
	    if($this->aggregatorName === null)
	    {
			$this->aggregatorName = Core::Database()
				->createQueryForClass($this)
				->call('getAggregatorName')
				->withParameters($this->compositeFor->getId())
				->fetchSingleValue();
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