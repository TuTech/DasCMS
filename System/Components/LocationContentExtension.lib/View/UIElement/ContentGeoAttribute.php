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
class View_UIElement_ContentGeoAttribute extends _View_UIElement 
{
    const CLASS_NAME = "View_UIElement_ContentGeoAttribute";
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
			$stat = Core::Database()
				->createQueryForClass(self::CLASS_NAME)
				->call('add')
				->withParameters($name, floatval($latitude), floatval($longitude))
				->execute();
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
			$stat = Core::Database()
				->createQueryForClass(self::CLASS_NAME)
				->call('delete')
				->withParameters($name)
				->execute();
        }
        catch (XDatabaseException $e)
        {
        }
        return $stat == 1;
    }

	protected static function load($call, $param){
		$res = Core::Database()
			->createQueryForClass(self::CLASS_NAME)
			->call($call)
			->withParameters($param);
		$row = $res->fetchResult();
		$res->free();
		if(!$row){
			throw new Exception('location not found');
		}
		return new View_UIElement_ContentGeoAttribute($row[0], $row[1], $row[2]);
	}

    public static function byName($name)
    {
		return self::load('get', $name);
    }
    
    public static function forContent(Interface_Content $content)
    {
        return self::load('getForContent', $content->getId());
    }
    
    public static function assignContentLocation(Interface_Content $content, $location)
    {
        $ret = null;
        try
        {
			Core::Database()
				->createQueryForClass(self::CLASS_NAME)
				->call('unlink')
				->withParameters($content->getId())
				->execute();
			$stat = Core::Database()
				->createQueryForClass(self::CLASS_NAME)
				->call('link')
				->withParameters($content->getId(), $location)
				->execute();
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
			$stat = Core::Database()
				->createQueryForClass(self::CLASS_NAME)
				->call('rename')
				->withParameters($name, $this->name)
				->execute();
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
			$stat = Core::Database()
				->createQueryForClass(self::CLASS_NAME)
				->call('relocate')
				->withParameters(floatval($latitude), floatval($longitude), $this->name)
				->execute();
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
    
    
    protected function __construct($name, $lat, $long)
    {
		$this->name = $name;
		$this->latitude = $lat;
		$this->longitude = $long;
    }

    /**
     * get render() output as string
     *
     * @return string
     */
    public function __toString()
    {
        $out = "<div class=\"View_UIElement_ContentGeoAttribute\">";
        if($this->name != null)
        {
            $out .= sprintf("<span class=\"WCGA-Name\">%s</span>".
            				"<span class=\"WCGA-Latitude\">%s</span>".
            				"<span class=\"WCGA-Longitude\">%s</span>"
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