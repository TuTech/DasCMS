<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2009-04-23
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage Widget
 */
class View_UIElement_ContentLocation extends _View_UIElement implements ISidebarWidget 
{
	private $targetObject = null;
	/**
	 * get an array of string of all supported classes 
	 * if it supports object, it supports all cms classes
	 * @return array
	 */
	public static function isSupported(View_UIElement_SidePanel $sidepanel)
	{
	    return (
	        $sidepanel->hasTarget()
	        && $sidepanel->isTargetObject()
	        && $sidepanel->isMode(View_UIElement_SidePanel::PROPERTY_EDIT)
	    );
	}
	
	public function getName()
	{
	    return 'content_location';
	}
	
	public function getIcon()
	{
	    return new View_UIElement_Icon('locate','',View_UIElement_Icon::SMALL,'action');
	}
	
	public function processInputs()
	{
	    try
	    {
	        if(RSent::hasValue('View_UIElement_ContentLocation_location'))
    	    {
    	        $loc = ULocations::getInstance();
    	        $lat = ''; 
    	        $long = '';
    	        if(RSent::hasValue('View_UIElement_ContentLocation_lat') 
    	            && RSent::hasValue('View_UIElement_ContentLocation_long'))
    	        {
    	            $conv = new Converter_GeoCoordinates(
        	            RSent::get('View_UIElement_ContentLocation_lat', CHARSET),
        	            RSent::get('View_UIElement_ContentLocation_long', CHARSET));
        	        list($lat,$long) = $conv->getDecimal();
    	        }
    	        $loc->setLocationData(
    	            RSent::get('View_UIElement_ContentLocation_location', CHARSET), 
    	            RSent::get('View_UIElement_ContentLocation_address', CHARSET),
    	            $lat,
    	            $long
    	        );
    	        $loc->setContentLocation(
    	            $this->targetObject->getAlias(), 
    	            RSent::get('View_UIElement_ContentLocation_location', CHARSET)
                );
    	    }
	    }
	    catch (Exception $e)
	    {
	        echo $e;
	        /*no new coords set*/
	    }
	}
	
	public function __construct(View_UIElement_SidePanel $sidepanel)
	{
		$this->targetObject = $sidepanel->getTarget();
	}
	
	public function __toString()
	{
	    $html = '';
	    try
	    {
    	    ob_start();
    	    $this->render();
    	    $html = strval(ob_get_clean());
	    }
	    catch (Exception $e)
	    {
	        $html = $e->getMessage();
	    }
		return $html;
	}
	
	public function render()
	{
	    $loc = ULocations::getInstance();
	    $location = $loc->getContentLocation($this->targetObject->getAlias());
	    $Items = new View_UIElement_NamedList();
		$Items->setTitleTranslation(false);
		$Items->add(   
		    sprintf("<label for=\"View_UIElement_ContentLocation_address\">%s</label>", SLocalization::get('address')),
		    sprintf('<textarea id="View_UIElement_ContentLocation_address" name="View_UIElement_ContentLocation_address">%s</textarea>', htmlentities($location['address'], ENT_QUOTES, CHARSET))
	    );
	    $lat ='';
	    $long ='';
	    try{
	        $conv = new Converter_GeoCoordinates($location['latitude'],$location['longitude']);
	        list($lat,$long) = $conv->getDMS();
	    }catch (Exception $e){}
		$Items->add(
		    sprintf("<label>%s</label>", SLocalization::get('gps_location')),
		    sprintf(
		    	'<dl><dt>%s</dt>'.
		    		'<dd><input type="text" id="View_UIElement_ContentLocation_lat" name="View_UIElement_ContentLocation_lat" value="%s" /></dd>'.
		        '<dt>%s</dt>'.
		    		'<dd><input type="text" id="View_UIElement_ContentLocation_long" name="View_UIElement_ContentLocation_long" value="%s" /></dd></dl>'
		    		, SLocalization::get('latitude')
		    		, htmlentities($lat, ENT_QUOTES, CHARSET)
		    		, SLocalization::get('longitude')
		    		, htmlentities($long, ENT_QUOTES, CHARSET)
	        )
		);
	    echo '<div id="View_UIElement_ContentLocation">'.
        		'<input type="hidden" id="View_UIElement_ContentLocation_location" name="View_UIElement_ContentLocation_location" value="',$this->targetObject->getGUID(),'" />'.
                $Items->__toString().
    		'</div>';
		
	}
	
	public function associatedJSObject(){return null;}
}
?>