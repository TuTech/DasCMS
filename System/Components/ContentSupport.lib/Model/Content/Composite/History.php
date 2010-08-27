<?php
class Model_Content_Composite_History 
	extends _Model_Content_Composite
	implements Interface_Composites_AutoAttach
{
    private $CreatedBy = '';
    private $CreateDate = 0;
    private $ModifiedBy = '';
    private $ModifyDate = 0;
	private $init = false;


	public static function getCompositeMethods()
    {
        return array('getCreatedBy', 'getModifiedBy', 'getCreateDate', 'getModifyDate');
    }
    
    public function __construct(Interface_Content $compositeFor)
    {
        parent::__construct($compositeFor);
    }

	protected function init(){
		if($this->init){
			return;
		}
		try
        {
			$res = Core::Database()
				->createQueryForClass($this)
				->call('latest')
				->withParameters($this->compositeFor->getId());
			list($changeDate, $this->ModifiedBy) = $res->fetchResult();
	  	    $this->ModifyDate = strtotime($changeDate);
 			$res->free();
			
			$res = Core::Database()
				->createQueryForClass($this)
				->call('created')
				->withParameters($this->compositeFor->getId());
			list($createDate, $this->CreatedBy) = $res->fetchResult();
    	    $this->CreateDate = strtotime($createDate);
			$res->free();
        }
        catch (Exception $e)
        {
            SErrorAndExceptionHandler::reportException($e);
        }
	}

	/**
	 * @return string
	 */
	public function getCreatedBy()
	{
		$this->init();
		return $this->CreatedBy;
	}
	
	/**
	 * @return string
	 */
	public function getModifiedBy()
	{
		$this->init();
		return $this->ModifiedBy;
	}
	
	/**
	 * @return int
	 */
	public function getCreateDate()
	{
		$this->init();
		return $this->CreateDate;
	}
	
	/**
	 * @return int
	 */
	public function getModifyDate()
	{
		$this->init();
		return $this->ModifyDate;
	}
} 
?>