<?php
class QAggregatorScopePage extends BQuery
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
        $sql = "SELECT COUNT(contentREL) 
        			FROM %s 
        			LEFT JOIN Contents ON (%s.contentREL = Contents.contentID)
        			WHERE 
        				contentAggregatorREL = %d
        				AND Contents.pubDate > '0000-00-00 00:00:00'
        				AND Contents.pubDate <= NOW()";
        $sql = sprintf($sql, $DB->escape($atbl), $DB->escape($atbl), $aid);
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
        $sql = "SELECT Aliases.alias
        			FROM %s 
        			LEFT JOIN Contents ON (%s.contentREL = Contents.contentID)
        			LEFT JOIN Aliases ON (Contents.primaryAlias = Aliases.aliasID)
        			WHERE 
        				contentAggregatorREL = %d
        				AND Contents.pubDate > '0000-00-00 00:00:00'
        				AND Contents.pubDate <= NOW()
        			LIMIT %d 
        			OFFSET %d";
        $sql = sprintf($sql, $DB->escape($atbl), $DB->escape($atbl), $aid, $offset, $limit);
        return $DB->query($sql, DSQL::NUM);
    }
}
?>