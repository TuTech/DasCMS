<?php
class QCFeed extends BQuery 
{
    /**
     * @param int $feedID
     * @return DSQLResult
     */
    public static function countItemsForFeed($feedID)
    {
        $sql = 
            "SELECT COUNT(*) 
				FROM relFeedsContents
					LEFT JOIN Contents ON (relFeedsContents.contentREL = Contents.contentID)
				WHERE 
					relFeedsContents.feedREL = %d
					AND Contents.pubDate != NULL
					AND Contents.pubDate <= NOW()";
        return BQuery::Database()->query(sprintf($sql, $feedID), DSQL::NUM);
    }
    
    /**
     * Contents.title, Contents.description, Contents.pubDate, Aliases.alias, Users.name, Contents.pubDate, concat(Tags.tag, ', ') 
     * 
     * @param int $page
     * @param int $itemsPerPage
     * @param array $props
     * @return DSQLResult
     */
    public static function getItemsForPage($feedID, $orderBY, $orderDesc ,$page, $itemsPerPage, array $props)
    {
        //FIXME optimize join and where for given props
        $sql = 
            "SELECT 
    				Contents.title,
    				Contents.description,
    				Contents.pubDate,
    				Aliases.alias,
					'-' AS 'Users.name',
					Contents.pubDate AS 'Changes.date',
    				GROUP_CONCAT(DISTINCT Tags.tag ORDER BY Tags.tag ASC SEPERATOR ', ')
				FROM relFeedsContents
    				LEFT JOIN Contents ON (relFeedsContents.contentREL = Contents.contentID)
    				LEFT JOIN Aliases ON (Contents.primaryAlias = Aliases.aliasID)
    				LEFT JOIN relContentsTags ON (Contents.contentID = relContentsTags.contentREL)
    				LEFT JOIN Tags ON (relContentsTags.tagREL = Tags.tagID)
				WHERE
					relFeedsContents.feedREL = %d
					AND Contents.pubDate != NULL
					AND Contents.pubDate <= NOW()
				GROUP BY Aliases.alias
				ORDER BY %s %s
				LIMIT %d
				OFFSET %d";
        $sql = sprintf(
            $sql
            ,$feedID
            ,strtolower($orderBY) == 'title' ? 'Contents.title' : 'Contents.pubDate'
			,$orderDesc ? 'DESC' : 'ASC'
			,$itemsPerPage
            ,($page-1)*$itemsPerPage 
        );
        return BQuery::Database()->query($sql, DSQL::NUM);
    }
}
?>