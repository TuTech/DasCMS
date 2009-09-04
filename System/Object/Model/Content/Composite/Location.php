<?php
class Model_Content_Composite_History extends _Model_Content_Composite
{
    /**
     * @var WContentGeoAttribute
     */
    private $Location;
    
    public function __construct(BContent $compositeFor)
    {
        parent::__construct($compositeFor);
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
	    $new = WContentGeoAttribute::assignContentLocation($this->compositeFor, $locationName);
	    if($new != null)
	    {
	        $this->Location = $new;
	    }
	}
} 
?>