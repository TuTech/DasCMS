<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2009-04-23
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage Query-MySQL
 */
class QWContentGeoAttribute extends BQuery 
{
    public static function add($name, $lat, $long)
    {
        $DB = BQuery::Database();
        $sql = "INSERT INTO Locations (location, latitude, longitude) VALUES ('%s', '%s', '%s')";
        $sql = sprintf($sql, $DB->escape($name), floatval($lat), floatval($long));
        return $DB->queryExecute($sql);
    }
    
    public static function rename($old, $new)
    {
        $DB = BQuery::Database();
        $sql = "UPDATE Locations SET location = '%s' WHERE location = '%s'";
        $sql = sprintf($sql, $DB->escape($new), $DB->escape($old));
        return $DB->queryExecute($sql);
    }
    
    public static function relocate($name, $lat, $long)
    {
        $DB = BQuery::Database();
        $sql = "UPDATE Locations SET latitude = '%s', longitude = '%s' WHERE location = '%s'";
        $sql = sprintf($sql, floatval($lat), floatval($long), $DB->escape($name));
        return $DB->queryExecute($sql);
    }
     
    public static function delete($name)
    {
        $DB = BQuery::Database();
        $sql = "DELETE FROM Locations WHERE location = '%s'";
        $sql = sprintf($sql, $DB->escape($name));
        return $DB->queryExecute($sql);
    }
    
    public static function getByName($name)
    {
        $DB = BQuery::Database();
        $sql = "SELECT location, latitude, longitude FROM Locations WHERE location = '%s'";
        $sql = sprintf($sql, $DB->escape($name));
        return $DB->query($sql, DSQL::NUM);
    }
        
    public static function getByContentId($id)
    {
        $DB = BQuery::Database();
        $sql = "SELECT 
        				Locations.location, Locations.latitude, Locations.longitude 
    				FROM Locations
    				LEFT JOIN relContentsLocations ON (relContentsLocations.locationREL = Locations.locationID) 
    				WHERE relContentsLocations.contentREL = %d";
        $sql = sprintf($sql, $id);
        return $DB->query($sql, DSQL::NUM);
    }
        
    public static function assignContentLocation($cid, $locationName)
    {
        $DB = BQuery::Database();
        $sql = "DELETE FROM relContentsLocations WHERE contentREL = %d";
        $DB->queryExecute(sprintf($sql, $cid));
        
        $sql = "INSERT INTO relContentsLocations (contentREL, locationREL)
        			SELECT %d AS 'contentREL', locationID FROM Locations WHERE location = '%s'";
        
        return $DB->queryExecute(sprintf($sql, $cid, $DB->escape($locationName)));
    }
    
}
?>