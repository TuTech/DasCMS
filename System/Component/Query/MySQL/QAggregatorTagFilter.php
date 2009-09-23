<?php
class QAggregatorTagFilter extends BQuery
{
    public static function removeAllContents($aId)
    {
        $sql = "DELETE FROM relAggregatorsContents WHERE contentAggregatorREL = %d";
        $sql = sprintf($sql, $aId);
        BQuery::Database()->queryExecute($sql);
    }
    
    /**
     * @param int $aId
     * @return DSQLResult
     */
    public static function countUnaggregatedContents($aId)
    {
        $sql = "SELECT COUNT(*) FROM ReaggregateContents WHERE contentAggregatorREL = %d";
        $sql = sprintf($sql, $aId);
        return BQuery::Database()->query($sql, DSQL::NUM);
    }
    
    public static function setAllContentsAggreagated($aId)
    {
        $sql = "DELETE FROM ReaggregateContents WHERE contentAggregatorREL = %d";
        $sql = sprintf($sql, $aId);
        BQuery::Database()->queryExecute($sql);
    }
    
    public static function aggregateMatch($aId, array $tags, $needsAll, $excluding = false)
    {
        $DB = BQuery::Database();
        $sql = "INSERT INTO relAggregatorsContents
        			(SELECT DISTINCT %d AS 'contentAggregatorREL', Contents.contentID AS 'contentREL'
        				FROM Tags
        				LEFT JOIN relContentsTags ON (relContentsTags.tagREL = Tags.tagID)
        				LEFT JOIN Contents ON (relContentsTags.contentREL = Contents.contentID)
        				WHERE NOT ISNULL(contentREL) AND (";
        $sql = sprintf($sql, $aId);
        if(count($tags))
        {
            //has a tag filter
            $etags = array();
            foreach ($tags as $tag)
            {
                $etags[] = sprintf('Tags.tag LIKE "%s"', $DB->escape($tag));
            }
            $sql .= implode(' OR ', $etags);
        }
        else
        {
            $sql .= '1';
        }
        if($needsAll)
        {
            $sql .= sprintf(") GROUP BY Contents.contentID HAVING COUNT(Tags.tagID) = %d)", count($tags));
        }
        else
        {
            $sql .= '))';
        }
        return $DB->queryExecute($sql);
    } 
    
    static function aggregateExcludeMatch($aId, array $tags, $needsAll)
    {
        $DB = BQuery::Database();
        $sql = "INSERT INTO relAggregatorsContents
        			(SELECT DISTINCT %d AS 'contentAggregatorREL', Contents.contentID AS 'contentREL'
        				FROM Contents WHERE Contents.contentID NOT IN 
            				(SELECT Contents.contentID  AS 'contentREL'
                				FROM Tags
                				LEFT JOIN relContentsTags ON (relContentsTags.tagREL = Tags.tagID)
                				LEFT JOIN Contents ON (relContentsTags.contentREL = Contents.contentID)
                				WHERE NOT ISNULL(contentREL) AND (";
        $sql = sprintf($sql, $aId);
        if(count($tags))
        {
            //has a tag filter
            $etags = array();
            foreach ($tags as $tag)
            {
                $etags[] = sprintf('Tags.tag LIKE "%s"', $DB->escape($tag));
            }
            $sql .= implode(' OR ', $etags);
        }
        else
        {
            $sql .= '1';
        }
        if($needsAll)
        {
            $sql .= sprintf(") GROUP BY Contents.contentID HAVING COUNT(Tags.tagID) = %d))", count($tags));
        }
        else
        {
            $sql .= ')))';
        }
        echo $sql;
        return $DB->queryExecute($sql);
    } 
    
}
?>