<?php
class Model_Content_Composite_Statistics 
	extends _Model_Content_Composite
	implements Interface_Composites_AutoAttach
{
    private $lastAccess = 0;
    private $accessCount = 0;
    private $accessIntervalAverage = 0;
    
    public static function getCompositeMethods()
    {
        return array('getLastAccess', 'getAccessCount', 'getAccessIntervalAverage');
    }
    
    public function __construct(Interface_Content $compositeFor)
    {
        parent::__construct($compositeFor);
        try
        {
			$res = Core::Database()
				->createQueryForClass($this)
				->call('stats')
				->withParameters($compositeFor->getId());
			$row = $res->fetchResult();
            if($row)
            {
                list(
                    $firstAccess,//ignore this
                    $this->lastAccess,
                    $this->accessCount,
                    $this->accessIntervalAverage
                ) = $row;
            }
            $res->free();
            $this->lastAccess = strtotime($this->lastAccess);
        }
        catch (Exception $e)
        {
            SErrorAndExceptionHandler::reportException($e);
        }
    }
    
	/**
	 * return last access timestamp
	 * @return int
	 */
    public function getLastAccess()
    {
        return $this->lastAccess;
    }
    
	/**
	 * @return int
	 */
    public function getAccessCount()
    {
        return $this->accessCount;
    }
    
	/**
	 * @return int
	 */
    public function getAccessIntervalAverage()
    {
        return $this->accessIntervalAverage;
    }
} 
?>