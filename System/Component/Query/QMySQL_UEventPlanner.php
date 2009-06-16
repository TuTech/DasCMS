<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2009-06-11
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage Query-MySQL
 */
class QUEventPlanner extends BQuery 
{
    /**
     * @param string $alias
     * @param int $begin
     * @param int $end
     * @param bool $fullDays
     * @return int
     */
    public static function scheduleEvent($alias, $begin, $end, $fullDays = false)
    {
        $DB = BQuery::Database();
        $sql = sprintf(
            "INSERT INTO EventDates (contentREL, startDate, endDate, fullDays) ".
                "(SELECT contentREL, '%s' as startDate, '%s' as endDate, '%s' as fullDays ".
                	"FROM Aliases WHERE alias = '%s')", 
            date('Y-m-d H:i:s', $begin), 
            date('Y-m-d H:i:s', $end), 
            $fullDays ? 'Y' : 'N',
            $DB->escape($alias)
        );
        return $DB->queryExecute($sql);
    }
    
    /**
     * @param string $alias
     * @return DSQLResult
     */
    public static function listEvents($alias)
    {
        $sql = "SELECT startDate, endDate, fullDays 
                	FROM EventDates
                	LEFT JOIN Aliases USING(contentREL)
                	WHERE alias = '%s'
                	ORDER BY startDate, endDate";
        $DB = BQuery::Database();
        return $DB->query(sprintf($sql, $DB->escape($alias)), DSQL::NUM);
    }
    
    /**
     * @param string $alias
     * @param int $begin
     * @param int $end
     * @return int
     */
    public static function removeEvent($alias, $begin, $end)
    {
        $DB = BQuery::Database();
        $sql = sprintf(
        	"DELETE FROM EventDates 
    			WHERE contentREL = (SELECT contentREL FROM Aliases WHERE alias = '%s')
    			AND startDate = '%s'
    			AND endDate = '%s'",
            $DB->escape($alias),
            date('Y-m-d H:i:s', $begin), 
            date('Y-m-d H:i:s', $end)
		);
		return $DB->queryExecute($sql);
    }
}
?>