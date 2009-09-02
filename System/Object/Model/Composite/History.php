<?php
class Model_Composite_History extends _Model_Composite
{
    private $CreatedBy = '';
    private $CreateDate = 0;
    private $ModifiedBy = '';
    private $ModifyDate = 0;
    
    public function __construct(BContent $compositeFor)
    {
        parent::__construct($compositeFor);
        try
        {
    	    list($cb, $cd, $mb, $md, $sz) = QBContent::getAdditionalMetaData($compositeFor->getAlias());
    	    $this->CreatedBy = $cb;
    	    $this->CreateDate = strtotime($cd);
    	    $this->ModifiedBy = $mb;
    	    $this->ModifyDate = strtotime($md);
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
		return $this->CreatedBy;
	}
	
	/**
	 * @return string
	 */
	public function getModifiedBy()
	{
		return $this->ModifiedBy;
	}
	
	/**
	 * @return int
	 */
	public function getCreateDate()
	{
		return $this->CreateDate;
	}
	
	/**
	 * @return int
	 */
	public function getModifyDate()
	{
		return $this->ModifyDate;
	}
} 
?>