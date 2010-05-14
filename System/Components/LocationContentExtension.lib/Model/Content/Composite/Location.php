<?php
class Model_Content_Composite_Location
	extends _Model_Content_Composite
	implements Interface_Composite_AutoAttach
{
    /**
     * @var WContentGeoAttribute
     */
    private $Location;
    
    public static function getCompositeMethods()
    {
        return array('getLocation', 'setLocation');
    }
    
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
	    $new = WContentGeoAttribute::assignContentLocation($this->compositeFor, $locationName);
	    if($new != null)
	    {
	        $this->Location = $new;
	    }
	}
} 
?>