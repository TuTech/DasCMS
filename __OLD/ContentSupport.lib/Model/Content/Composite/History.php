<?php
class Model_Content_Composite_History 
	extends _Model_Content_Composite
	implements Interface_Composites_AutoAttach
{
    private $createdBy = '';
    private $createDate = 0;
    private $modifiedBy = '';
    private $modifyDate = 0;
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
		$this->init = true;
		try
        {
			$res = Core::Database()
				->createQueryForClass($this)
				->call('latest')
				->withParameters($this->compositeFor->getId());
			list($changeDate, $this->modifiedBy) = $res->fetchResult();
	  	    $this->modifyDate = strtotime($changeDate);
 			$res->free();
			
			$res = Core::Database()
				->createQueryForClass($this)
				->call('created')
				->withParameters($this->compositeFor->getId());
			list($createDate, $this->createdBy) = $res->fetchResult();
    	    $this->createDate = strtotime($createDate);
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
		return $this->createdBy;
	}
	
	/**
	 * @return string
	 */
	public function getModifiedBy()
	{
		$this->init();
		return $this->modifiedBy;
	}
	
	/**
	 * @return int
	 */
	public function getCreateDate()
	{
		$this->init();
		return $this->createDate;
	}
	
	/**
	 * @return int
	 */
	public function getModifyDate()
	{
		$this->init();
		return $this->modifyDate;
	}
} 
?>