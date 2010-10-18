<?php
class Model_Content_Composite_Location
	extends _Model_Content_Composite
	implements Interface_Composites_AutoAttach
{
    /**
     * @var View_UIElement_ContentGeoAttribute
     */
    private $Location;
    
    public static function getCompositeMethods()
    {
        return array('getLocation', 'setLocation');
    }
    
    public function __construct(Interface_Content $compositeFor)
    {
        parent::__construct($compositeFor);
        try
        {
    	    $this->Location = View_UIElement_ContentGeoAttribute::forContent($compositeFor);
        }
        catch (Exception $e)
        {
            SErrorAndExceptionHandler::reportException($e);
        }
    }
    
	/**
	 * @return View_UIElement_ContentGeoAttribute
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
	    $new = View_UIElement_ContentGeoAttribute::assignContentLocation($this->compositeFor, $locationName);
	    if($new != null)
	    {
	        $this->Location = $new;
	    }
	}
} 
?>