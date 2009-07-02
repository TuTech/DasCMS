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
class WContentLocation extends BWidget implements ISidebarWidget 
{
	private $targetObject = null;
	/**
	 * get an array of string of all supported classes 
	 * if it supports BObject, it supports all cms classes
	 * @return array
	 */
	public static function isSupported(WSidePanel $sidepanel)
	{
	    return (
	        $sidepanel->hasTarget()
	        && $sidepanel->isTargetObject()
	        && $sidepanel->isMode(WSidePanel::PROPERTY_EDIT)
	    );
	}
	
	public function getName()
	{
	    return 'content_location';
	}
	
	public function getIcon()
	{
	    return new WIcon('locate','',WIcon::SMALL,'action');
	}
	
	public function processInputs()
	{
	    try
	    {
    	    if(RSent::hasValue('WContentLocation_location'))
    	    {
    	        $loc = ULocations::getSharedInstance();
    	        $loc->setLocationData(
    	            RSent::get('WContentLocation_location', CHARSET), 
    	            RSent::get('WContentLocation_address', CHARSET),
    	            RSent::get('WContentLocation_lat', CHARSET),
    	            RSent::get('WContentLocation_long', CHARSET)
    	        );
    	        $loc->setContentLocation(
    	            $this->targetObject->getAlias(), 
    	            RSent::get('WContentLocation_location', CHARSET)
                );
    	    }
	    }
	    catch (Exception $e)
	    {
	        echo $e->getMessage();
	    }
	}
	
	public function __construct(WSidePanel $sidepanel)
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
	    $loc = ULocations::getSharedInstance();
	    $location = $loc->getContentLocation($this->targetObject->getAlias());
	    $Items = new WNamedList();
		$Items->setTitleTranslation(false);
		$Items->add(   
		    sprintf("<label for=\"WContentLocation_address\">%s</label>", SLocalization::get('address')),
		    sprintf('<textarea id="WContentLocation_address" name="WContentLocation_address">%s</textarea>', htmlentities($location['address'], ENT_QUOTES, CHARSET))
	    );
		$Items->add(
		    sprintf("<label>%s</label>", SLocalization::get('gps_location_in_decimal')),
		    sprintf(
		    	'<dl><dt>%s</dt>'.
		    		'<dd><input type="text" id="WContentLocation_lat" name="WContentLocation_lat" value="%s" /></dd>'.
		        '<dt>%s</dt>'.
		    		'<dd><input type="text" id="WContentLocation_long" name="WContentLocation_long" value="%s" /></dd></dl>'
		    		, SLocalization::get('latitude')
		    		, htmlentities($location['latitude'], ENT_QUOTES, CHARSET)
		    		, SLocalization::get('longitude')
		    		, htmlentities($location['longitude'], ENT_QUOTES, CHARSET)
	        )
		);
	    echo '<div id="WContentLocation">'.
        		'<input type="hidden" id="WContentLocation_location" name="WContentLocation_location" value="',$this->targetObject->getGUID(),'" />'.
                $Items->__toString().
    		'</div>';
		
	}
	
	public function associatedJSObject(){return null;}
}
?>