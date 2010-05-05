<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2009-06-19
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage Query-MySQL
 */
class QULocations extends BQuery 
{
    public static function getContentLocation($alias)
    {
        $sql = "SELECT 	location, 
        				IF(ISNULL(latitude),'',latitude), 
        				IF(ISNULL(longitude),'',longitude), 
        				IF(ISNULL(address),'',address)
                	FROM Aliases
                	LEFT JOIN relContentsLocations USING (contentREL)
                	LEFT JOIN Locations ON (relContentsLocations.locationREL = Locations.locationID)
                	WHERE Aliases.alias = '%s'";
        $DB = BQuery::Database();
        return $DB->query(sprintf($sql, $DB->escape($alias)), DSQL::NUM);
    }
    
    public static function setContentLocation($alias, $location)
    {
        $validLoc = false;
        $locId = 0;
        //validate location and get location id
        $sql = "SELECT locationID FROM Locations WHERE location = '%s'";
        $DB = BQuery::Database();
        $res = $DB->query(sprintf($sql, $DB->escape($location)), DSQL::NUM);
        if($res->getRowCount() == 1)
        {
            $validLoc = true;
            list($locId) = $res->fetch();
        }
        $res->free();
        
        if($validLoc)
        {
            //set location
            $sql = "INSERT INTO relContentsLocations (contentREL, locationREL) 
            			VALUES(
        					(SELECT contentREL FROM Aliases WHERE alias = '%s'), %d
        				)
            			ON DUPLICATE KEY UPDATE locationREL = %d";
            $sql = sprintf($sql, $DB->escape($alias), $locId, $locId);
        }
        else
        {
            //remove location
            $sql = "DELETE 
                    	FROM relContentsLocations 
                    	WHERE relContentsLocations.contentREL = (SELECT contentREL FROM Aliases WHERE alias = '%s')";
            $sql = sprintf($sql, $DB->escape($alias), $locId);
        }
        return $DB->queryExecute($sql);
    }
        
    public static function setLocationData($location, $address, $latitude, $longitude)
    {
        $DB = BQuery::Database();
        //set location
        $sql = "INSERT INTO Locations (location, latitude, longitude, address) 
        			VALUES('%s', %s, %s, %s)
        			ON DUPLICATE KEY UPDATE 
        				latitude = %s,
        				longitude = %s,
        				address = %s";
        $address = empty($address) ? 'NULL' : "'".$DB->escape($address)."'";
        $latitude = empty($latitude) || !is_numeric($latitude) ? 'NULL' : floatval($latitude);
        $longitude = empty($longitude) || !is_numeric($longitude) ? 'NULL' : floatval($longitude);
        
        $sql = sprintf($sql, $DB->escape($location), $latitude, $longitude, $address, $latitude, $longitude, $address);
        
        return $DB->queryExecute($sql);
    }
    
    /**
     * @param string $alias
     * @return DSQLResult
     */
    public static function getLocationList($query, $limit = 0)
    {
        $sql = "SELECT location 
                	FROM Locations
                	WHERE location = '%%%s%%'
                	ORDER BY location";
        if($limit)
        {
            $sql .= ' LIMIT '.intval($limit);
        }
        $DB = BQuery::Database();
        return $DB->query(sprintf($sql, $DB->escape($query)), DSQL::NUM);
    }
}
?>