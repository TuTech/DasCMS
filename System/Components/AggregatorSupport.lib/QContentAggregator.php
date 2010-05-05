<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2009-09-23
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage Query-MySQL
 */
class QContentAggregator extends BQuery 
{
    /**
     * result has exactly one row
     * @param string $name
     * @return DSQLResult 
     */
    public static function getAggregatorName($cid)
    {
        $sql = "SELECT ContentAggregators.name 
        			FROM ContentAggregators
        			LEFT JOIN relContentsAggregator ON (contentAggregatorID = contentAggregatorREL) 
        			WHERE contentREL = %d";
        $sql = sprintf($sql, $cid);
        return BQuery::Database()->query($sql, DSQL::NUM);
    }
        
    public static function setAggregator($cid, $aggregatorname)
    {
        $DB = BQuery::Database();
        $DB->beginTransaction();
        $DB->queryExecute(sprintf('DELETE FROM relContentsAggregator WHERE contentREL = %d', $cid));
        $res = $DB->queryExecute(sprintf(
        	"INSERT INTO relContentsAggregator 
        		SELECT %d as contentREL, contentAggregatorID as contentAggregatorREL 
        		FROM ContentAggregators 
        		WHERE ContentAggregators.name = '%s'", $cid, $DB->escape($aggregatorname)));
        $DB->commit();
        return $res;
    }
}
?>