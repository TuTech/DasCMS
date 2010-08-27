<?php
class Model_Content_Composite_Statistics 
	extends _Model_Content_Composite
	implements Interface_Composites_AutoAttach
{
    private $LastAccess = 0;
    private $AccessCount = 0;
    private $AccessIntervalAverage = 0;
    
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
                    $this->LastAccess,
                    $this->AccessCount,
                    $this->AccessIntervalAverage
                ) = $row;
            }
            $res->free();
            $this->LastAccess = strtotime($this->LastAccess);
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
        return $this->LastAccess;
    }
    
	/**
	 * @return int
	 */
    public function getAccessCount()
    {
        return $this->AccessCount;
    }
    
	/**
	 * @return int
	 */
    public function getAccessIntervalAverage()
    {
        return $this->AccessIntervalAverage;
    }
} 
?>