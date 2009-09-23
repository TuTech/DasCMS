<?php
class Controller_Aggregators
    implements  HContentChangedEventHandler,
                HContentCreatedEventHandler
{	
    //IShareable
	const CLASS_NAME = 'Controller_Aggregators';
	
	/**
	 * @var Controller_Aggregators
	 */
	public static $sharedInstance = NULL;
	
	/**
	 * @return Controller_Aggregators
	 */
	public static function getSharedInstance()
	{
		$class = self::CLASS_NAME;
		if(self::$sharedInstance == NULL && $class != NULL)
		{
			self::$sharedInstance = new $class();
		}
		return self::$sharedInstance;
	}
	//end IShareable
    
    //cud
    public function HandleContentChangedEvent(EContentChangedEvent $e)
    {
        //schedule check of id
        QControllerAggregators::reaggregateContent($e->Content->getId());
    }
    
    public function HandleContentCreatedEvent(EContentCreatedEvent $e)
    {
        //schedule check of id
        QControllerAggregators::reaggregateContent($e->Content->getId());
    }
    
    protected function handleAggregatorChanged($aggregatorName)
    {
        $res = QControllerAggregators::loadAggregator($aggregatorName);
        if($res->getRowCount() != 1)
        {
            throw new XUndefinedException('aggregator not found');
        }
        list(
            $class,
            $id,
            $data
        ) = $res->fetch();
        $res->free();
        QControllerAggregators::reaggregateAggregator($id);
    }
    
    public function saveAggregator(_Aggregator $aggregator, $newName = null)
    {
        if($aggregator->getType() == _Aggregator::DYNAMIC)
        {
            throw new XArgumentException('can not save dynamic aggregators');
        }
        $name = $aggregator->getAggregatorName();
        $new = false;
        if($name === false)
        {
            $name = substr($newName,0,32);
            $new = true;
        }
        if(!$name)
        {
            throw new Exception('aggregater has no name');
        }
        //dump class
        $class = get_class($aggregator);
        $data = serialize($aggregator);

        $res = QControllerAggregators::getClassRef($class);
        if($res->getRowCount() != 1)
        {
            throw new Exception('no class ref');
        }
        list($classID) = $res->fetch();
        $res->free();
        
        $rows = 0;
        if($new)
        {
            echo '*new*';
            $rows = QControllerAggregators::insertAggregator($classID, $name, $data);
        }
        else
        {
            $rows = QControllerAggregators::updateAggregator($classID, $name, $data);
        }
        if($rows)
        {
            $this->handleAggregatorChanged($name);
        }
        return $rows;
    }
    
    /**
     * 
     * @param string $name
     * @return _Aggregator
     */
    public function getSavedAggregator($name)
    {
        $res = QControllerAggregators::loadAggregator($name);
        if($res->getRowCount() != 1)
        {
            throw new XUndefinedException('aggregator not found');
        }
        list(
            $class,
            $id,
            $data
        ) = $res->fetch();
        $res->free();
        if(!class_exists($class, true))
        {
            throw new XUndefinedException('aggregator class not found');
        }
        SErrorAndExceptionHandler::muteErrors();
        $aggregator = unserialize($data);
        SErrorAndExceptionHandler::reportErrors();
        if(!is_object($aggregator) || !$aggregator instanceof _Aggregator)
        {
            throw new XInvalidDataException('could not load aggregator');
        }
        $aggregator->initDatabaseAssociation($name, $id);
        return $aggregator;
    }
    
    public function updateOutdatedAggregators()
    {
        $res = QControllerAggregators::getListOfOutdated();
        $aggregators = array();
        while (list($name) = $res->fetch())
        {
            $aggregators[] = $name;
        }
        $res->free();
        if(count($aggregators))
        {
            $cur = array_pop($aggregators);
            echo 'aggeregating '.$cur;
            $this->getSavedAggregator($cur)->aggregate();
        }
    }
    
    public function getListOfSavedAggregators()
    {
        $res = QControllerAggregators::getList();
        $aggregators = array();
        while (list($name) = $res->fetch())
        {
            $aggregators[] = $name;
        }
        $res->free();
        return $aggregators;
    }
}
?>