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
class WContentGeoAttribute extends BWidget 
{
    const CLASS_NAME = "WContentGeoAttribute";
    private $name = null;
    private $latitude = null;
    private $longitude = null;
    
    private static function validateCoord($coord, $range, $pos, $neg)
    {
        //unify
        $coord = strtoupper(trim($coord));
        //get appendix (N, S, E, W)
        $lc = substr($coord, -1);
        if($lc == $pos || $lc == $neg)
        {
            $coord = substr($coord, 0, -1);
        }
        //make numeric
        $coord = floatval($coord);
        //invert if negative
        if($lc == $neg)
        {
            $coord *= -1;
        }
        //
        while ($coord > $range || $coord < ($range*-1))
        {
            //remove $range degree and invert
            $coord = ($coord > $range ? $coord - $range : $coord + $range) * -1;
        }
        return $coord;
    }
    
    public static function create($name, $latitude, $longitude)
    {
        $latitude = self::validateCoord($latitude, 90, 'N', 'S');
        $longitude = self::validateCoord($longitude, 180, 'E', 'W');
        $ret = null;
        try
        {
            $stat = QWContentGeoAttribute::add($name, $latitude, $longitude);
            if($stat)
            {
                $ret = self::byName($name);
            }
        }
        catch (XDatabaseException $e)
        {
        }
        return $ret;
    }
    
    public static function delete($name)
    {
        $stat = false;
        try
        {
            $stat = QWContentGeoAttribute::delete($name);
        }
        catch (XDatabaseException $e)
        {
        }
        return $stat == 1;
    }
    
    public static function byName($name)
    {
        return new WContentGeoAttribute(QWContentGeoAttribute::getByName($name));
    }
    
    public static function forContent(Interface_Content $content)
    {
        return new WContentGeoAttribute(QWContentGeoAttribute::getByContentId($content->getId()));
    }
    
    public static function assignContentLocation(BContent $content, $location)
    {
        $ret = null;
        try
        {
            $stat = QWContentGeoAttribute::assignContentLocation($content->getId(), $location);
            if($stat)
            {
                $ret = self::byName($location); 
            }
        }
        catch (XDatabaseException $e)
        {
        }
        return $ret;
    }
    
    public function rename($name)
    {
        $stat = false;
        try
        {
            $stat = QWContentGeoAttribute::rename($this->name, $name);
            $this->name = $name;
        }
        catch (XDatabaseException $e)
        {
        }
        return $stat == 1;
    }
    
    public function relocate($latitude, $longitude)
    {
        $latitude = self::validateCoord($latitude, 90, 'N', 'S');
        $longitude = self::validateCoord($longitude, 180, 'E', 'W');
        $stat = false;
        try
        {
            $stat = QWContentGeoAttribute::relocate($this->name, $latitude, $longitude);
        }
        catch (XDatabaseException $e)
        {
        }
        return $stat == 1;
    }
    
    public function getName()
    {
        return $this->name;
    }
    
    public function getLatitude()//N/S
    {
        return floatval($this->latitude);
    }
    
    public function getLongitude()//E/W
    {
        return floatval($this->longitude);
    }
    
    
    protected function __construct(DSQLResult $res)
    {       
        if($res->getRowCount() == 1)
        {
            list(
                $this->name,
                $this->latitude,
                $this->longitude
            ) = $res->fetch();
        }
        $res->free();
    }

    /**
     * get render() output as string
     *
     * @return string
     */
    public function __toString()
    {
        $out = "<div class=\"WContentGeoAttribute\">";
        if($this->name != null)
        {
            $out .= sprintf("\n\t<span class=\"WCGA-Name\">%s</span>\n\t".
            				"<span class=\"WCGA-Latitude\">%s</span>\n\t".
            				"<span class=\"WCGA-Longitude\">%s</span>\n"
            	,htmlentities($this->name, ENT_QUOTES, CHARSET)
            	,htmlentities($this->latitude, ENT_QUOTES, CHARSET)
            	,htmlentities($this->longitude, ENT_QUOTES, CHARSET)
        	);
        }
        $out .= "</div>";
        return $out;
    }
}
?>