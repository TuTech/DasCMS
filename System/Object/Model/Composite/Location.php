<?php
class Model_Composite_History extends _Model_Composite
{
    /**
     * @var WContentGeoAttribute
     */
    private $Location;
    /**
     * @var BContent
     */
    private $content;
    
    public function __construct(BContent $compositeFor)
    {
        parent::__construct($compositeFor);
        $this->content = $compositeFor;
        try
        {
    	    $this->Location = WContentGeoAttribute::forContent($compositeFor);
        }
        catch (Exception $e)
        {
            SErrorAndExceptionHandler::reportException($e);
        }
    }
    
	/**
	 * @return WContentGeoAttribute
	 */
	public function getLocation()
	{
		return $this->Location;
	}
	
	/**
	 * save new location
	 * @param $locationName
	 * @return void
	 */
	public function setLocation($locationName)
	{
	    #echo '<h1>setting</h1>';
	    $new = WContentGeoAttribute::assignContentLocation($this->content, $locationName);
	    if($new != null)
	    {
	        $this->Location = $new;
	    }
	}
} 
?>