<?php
class QAggregatorScopeEventPage extends BQuery
{
    /**
     * @param string $atbl
     * @param int $aid
     * @return DSQLResult
     */
    public static function countItems($atbl, $aid)
    {
        //FIXME consider tag permissions
        $DB = BQuery::Database();
        $sql = "SELECT COUNT(contentID) 
        			FROM %s 
        			LEFT JOIN Contents ON (%s.contentREL = Contents.contentID)
        			LEFT JOIN EventDates ON (EventDates.contentREL = Contents.contentID)
        			WHERE 
        				contentAggregatorREL = %d
        				AND Contents.pubDate > '0000-00-00 00:00:00'
        				AND Contents.pubDate <= NOW()
                        AND NOT ISNULL(EventDates.startDate)
                        AND NOT ISNULL(EventDates.endDate)
                        AND EventDates.endDate >= '%s 00:00:00'";
        $sql = sprintf($sql, $DB->escape($atbl), $DB->escape($atbl), $aid, date('Y-m-d'));
        return $DB->query($sql, DSQL::NUM);
    }
    
    /**
     * @param string $atbl
     * @param int $aid
     * @return DSQLResult
     */
    public static function fetchItems($atbl, $aid, $offset, $limit, $fieldToSortBy, $sortOrder, $tableToJoinForSort = null)
    {
        //FIXME consider tag permissions
        //FIXME allow cusom joins
        //FIXME allow sort order/field
        
        $DB = BQuery::Database();
        $sql = "SELECT Aliases.alias, EventDates.startDate, EventDates.endDate
        			FROM %s 
        			LEFT JOIN Contents ON (%s.contentREL = Contents.contentID)
        			LEFT JOIN Aliases ON (Contents.primaryAlias = Aliases.aliasID)
        			LEFT JOIN EventDates ON (EventDates.contentREL = Contents.contentID)
        			WHERE 
        				contentAggregatorREL = %d
        				AND Contents.pubDate > '0000-00-00 00:00:00'
        				AND Contents.pubDate <= NOW()
        				AND NOT ISNULL(startDate)
        				AND NOT ISNULL(endDate)
                        AND EventDates.endDate >= '%s 00:00:00'
    				ORDER BY EventDates.startDate, EventDates.endDate
        			LIMIT %d 
        			OFFSET %d";
        $sql = sprintf($sql, $DB->escape($atbl), $DB->escape($atbl), $aid, date('Y-m-d'), $limit, $offset);
        return $DB->query($sql, DSQL::NUM);
    }
}
?>