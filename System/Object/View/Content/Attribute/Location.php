<?php
class View_Content_Attribute_Location extends _View_Content_Attribute
{
    protected $latitude = null;
    protected $longitude = null;
    protected $address = null;
    
    public function __construct($aliasOrContent)
    {
        try{
            $alias = ($aliasOrContent instanceof BContent)
                ? $aliasOrContent->getAlias()
                : $aliasOrContent;
            $loc = ULocations::getSharedInstance();
            $loc = $loc->getContentLocation($alias);;
    	    list(
    	        $locName,
    	        $this->latitude,
    	        $this->longitude,
    	        $this->address
    	    ) = array_values($loc);
        }catch (Exception $e){echo $e->getTraceAsString();/**/}
    }
    
    public function __toString()
    {
        $str = '';
        try
        {
            if($this->latitude !== null && $this->longitude !== null)
            {
                $conv = new Converter_GeoCoordinates($this->latitude, $this->longitude);
                list($lat,$long) = $conv->getDMS();
                $str .= sprintf(
                	"\t<abbr class=\"latitude\" title=\"%f\">%s</abbr>\n". 
        			"\t<abbr class=\"longitude\" title=\"%f\">%s</abbr>\n"
        			,$this->latitude
        			,$lat
            		,$this->longitude	
            		,$long
        	   	);
            }
        }catch (Exception $e){/*ignore invalid coords*/}
	   	if(!empty($this->address))
	   	{
	   	    $str .= "\t<address>".$this->address."</address>\n";
	   	}
   	    $str = "<div class=\"geo\">\n".$str."</div>\n";
	   	return $str;
    }
}
?>