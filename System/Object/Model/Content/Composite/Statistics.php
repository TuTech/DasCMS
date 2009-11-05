<?php
class Model_Content_Composite_Statistics extends _Model_Content_Composite
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
            $res = QBContent::getAccessStats($compositeFor->getId());
            if($res->getRowCount() > 0)
            {
                list(
                    $firstAccess,//ignore this
                    $this->LastAccess,
                    $this->AccessCount,
                    $this->AccessIntervalAverage
                ) = $res->fetch();
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