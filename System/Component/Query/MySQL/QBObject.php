<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2008-11-07
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage Query-MySQL
 */
class QBObject extends BQuery 
{
    public static function getClassName($guid)
    {
        $sql = "
            SELECT class 
            	FROM Classes
            	WHERE guid = '%s'";
        $DB = BQuery::Database();
        $res = $DB->query(sprintf($sql, $DB->escape($guid)), DSQL::NUM);
        if($res->getRowCount() != 1)
        {
            throw new XUndefinedIndexException($guid);
        }
        list($class) = $res->fetch();
        return $class;
    }
    
    public static function preloadClassLookup()
    {
        $sql = "
            SELECT guid, class 
            	FROM Classes
            	WHERE LENGTH(guid) > 0";
        $DB = BQuery::Database();
        $res = $DB->query($sql, DSQL::NUM);
        $index = array();
        while ($row = $res->fetch())
        {
        	$index[$row[0]] = $row[1];
        }
        return $index;
    }
}
?>