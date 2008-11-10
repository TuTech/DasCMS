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
					AND relFeedsContents.feedREL != relFeedsContents.contentREL
					AND Contents.pubDate > 0
					AND Contents.pubDate <= NOW()";
        return BQuery::Database()->query(sprintf($sql, $feedID), DSQL::NUM);
    }
    
    public static function setFeedType($feedId, $filterType)
    {
        $type = array_search($filterType, array('',CFeed::ALL, CFeed::MATCH_SOME, CFeed::MATCH_ALL, CFeed::MATCH_NONE));
        $sql = 
            "INSERT INTO Feeds 
					(contentREL, filterType, lastUpdate, associatedItems)
				VALUES 
					(%d, %d, NOW(), 0)
				ON DUPLICATE KEY UPDATE 
                    filterType = %d,
                    lastUpdate = NOW()";
        BQuery::Database()->queryExecute(sprintf($sql, $feedId, $type, $type));
    }
    
    public static function setFilterTags($feedId, array $tags)
    {
        $DB = BQuery::Database();
        try{
            $DB->queryExecute(sprintf("DELETE FROM relFeedsTags WHERE feedREL = %d",$feedId));
			
            $tagval = array();
			foreach ($tags as $tag) 
			{
				$tagval[] = array($tag);
			}
			if(count($tagval) > 0)
			{
				BQuery::Database()->insert('Tags',array('tag'),$tagval, true);
			}	
				
            foreach ($tags as $tag) 
    		{
    			$DB->insertUnescaped(
    				'relFeedsTags',
    				array('feedREL', 'tagREL'),
    				array(
    					$DB->escape($feedId),
    					"(SELECT tagID FROM Tags WHERE tag = '".$DB->escape($tag)."')"
    				),
    				true
    			);
    		}       
        }
        catch (Exception $e)
	    {
	        echo "\n\n<!--Exception\n".$e->getFile().'@'.$e->getLine()."\n";
	        echo $e->getMessage()."\n";
	        echo $e->getTraceAsString();
	        echo " -->\n";
	    } 
    }
    
    /**
     * Contents.title, Contents.description, Contents.pubDate, Aliases.alias, Users.name, Changes.date, concat(Tags.tag, ', ') 
     * 
     * @param int $feedID
     * @param string $orderBY
     * @param bool $orderDesc
     * @param int $page
     * @param int $itemsPerPage
     * @param array $props
     * @return DSQLResult
     */
    public static function getItemsForPage($feedID, $orderBY, $order ,$page, $itemsPerPage, array $props)
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
    				GROUP_CONCAT(DISTINCT Tags.tag ORDER BY Tags.tag ASC SEPARATOR ', ')
				FROM relFeedsContents
    				LEFT JOIN Contents ON (relFeedsContents.contentREL = Contents.contentID)
    				LEFT JOIN Aliases ON (Contents.primaryAlias = Aliases.aliasID)
    				LEFT JOIN relContentsTags ON (Contents.contentID = relContentsTags.contentREL)
    				LEFT JOIN Tags ON (relContentsTags.tagREL = Tags.tagID)
				WHERE
					relFeedsContents.feedREL = %d
					AND relFeedsContents.feedREL != relFeedsContents.contentREL
					AND Contents.pubDate > 0
					AND Contents.pubDate <= NOW()
				GROUP BY Aliases.alias
				ORDER BY %s %s
				LIMIT %d
				OFFSET %d";
        $sql = sprintf(
            $sql
            ,$feedID
            ,strtolower($orderBY) == 'title' ? 'Contents.title' : 'Contents.pubDate'
			,$order ? 'DESC' : 'ASC'
			,$itemsPerPage
            ,($page-1)*$itemsPerPage 
        );
        return BQuery::Database()->query($sql, DSQL::NUM);
    }
    
    /**
     * Contents.title, Contents.description, Contents.pubDate, Aliases.alias, Changes.date, concat(Tags.tag, ', ') 
     * 
     * @param int $feedID
     * @return DSQLResult
     */
    public static function getItemsForFeed($feedID)
    {
        //FIXME optimize join and where for given props
        $sql = 
            "SELECT 
    				Contents.title,
    				Contents.description,
    				Contents.pubDate,
    				Aliases.alias,
					Contents.pubDate AS 'Changes.date',
    				GROUP_CONCAT(DISTINCT Tags.tag ORDER BY Tags.tag ASC SEPARATOR ', '),
					Classes.class,
					Mimetypes.mimetype,
					Contents.size
				FROM relFeedsContents
    				LEFT JOIN Contents ON (relFeedsContents.contentREL = Contents.contentID)
    				LEFT JOIN Aliases ON (Contents.primaryAlias = Aliases.aliasID)
    				LEFT JOIN relContentsTags ON (Contents.contentID = relContentsTags.contentREL)
    				LEFT JOIN Tags ON (relContentsTags.tagREL = Tags.tagID)
    				LEFT JOIN Classes ON (Contents.type = Classes.classID)
    				LEFT JOIN Mimetypes ON (Contents.mimetypeREL = Mimetypes.mimetypeID)
				WHERE
					relFeedsContents.feedREL = %d
					AND relFeedsContents.feedREL != relFeedsContents.contentREL
					AND Contents.pubDate > 0
					AND Contents.pubDate <= NOW()
				GROUP BY Aliases.alias
				ORDER BY Contents.pubDate DESC
				LIMIT 15";
        $sql = sprintf($sql, $feedID);
        return BQuery::Database()->query($sql, DSQL::NUM);
    }
}
?>