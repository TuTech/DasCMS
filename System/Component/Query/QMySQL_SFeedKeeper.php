<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2008-10-22
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage Query-MySQL
 */
class QSFeedKeeper extends BQuery 
{
    public static function updateStats($feedId)
    {
        $sql = 
            "UPDATE Feeds 
				SET lastUpdate = NOW(), 
					associatedItems = (SELECT COUNT(*) FROM relFeedsContents WHERE feedREL = %d)
				WHERE contentREL = %d";
        BQuery::Database()->queryExecute(sprintf($sql, $feedId, $feedId));
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
    
    public static function linkItem($itemId, array $feedIds)
    {
        if(count($feedIds) == 0)
        {
            return;
        }
        $sep = '';
        $sql = 
            "INSERT IGNORE INTO relFeedsContents (feedREL, contentREL) 
				VALUES";
        foreach ($feedIds as $id) 
        {
            if($id == $itemId)
            {
                continue;
            }
        	$sql .= sprintf('%s (%d, %d)', $sep, $id, $itemId);
        	$sep = ',';
        }
        BQuery::Database()->queryExecute($sql);
    }
    
    public static function clearFeed($feedId)
    {
        $sql = "DELETE FROM relFeedsContents WHERE feedREL = %d";
        BQuery::Database()->queryExecute(sprintf($sql, $feedId));
    }
    
    public static function unlinkItem($itemId)
    {
        $sql = "DELETE FROM relFeedsContents WHERE contentREL = %d";
        BQuery::Database()->queryExecute(sprintf($sql, $itemId));
    }
    
    /**
     * @return DSQLResult
     */
    public static function getFeedType($feedId)
    {
        $sql = "SELECT filterType FROM Feeds WHERE contentREL = %d";
        return BQuery::Database()->query(sprintf($sql, $feedId), DSQL::NUM);
    }
    
    /**
     * @return DSQLResult
     */
    public static function getFeedsWithType()
    {
        $sql = "SELECT contentREL,filterType FROM Feeds";
        return BQuery::Database()->query($sql, DSQL::NUM);
    }
    
    /**
     * @return DSQLResult
     */
    public static function getFeedsWithTypeAndTags()
    {
        $sql = "SELECT 
						Feeds.contentREL, Feeds.filterType, Tags.tag 
					FROM Feeds 
					LEFT JOIN relFeedsTags ON (Feeds.contentREL = relFeedsTags.feedREL)
					LEFT JOIN Tags ON (relFeedsTags.tagREL = Tags.tagID)";
        return BQuery::Database()->query($sql, DSQL::NUM);
    }
    
    /**
     * @return void
     */
    public static function assignItemsUsingAll($feedId)
    {
        //insert select
        $sql = 
            "INSERT 
				INTO relFeedsContents
				SELECT %d AS feedREL, contentID AS contentREL
					FROM Contents
					WHERE contentID != %d";
        BQuery::Database()->queryExecute(sprintf($sql, $feedId, $feedId));
    }
    
    public static function assignItemsUsingMatchSome($feedId)
    {
        //insert select
        $sql = 
            "INSERT 
				INTO relFeedsContents
				SELECT %d AS feedREL, contentREL
					FROM relContentsTags
					WHERE tagREL IN
					(
						SELECT tagREL 
							FROM relFeedsTags
							WHERE feedREL = %d
					)";
        BQuery::Database()->queryExecute(sprintf($sql, $feedId, $feedId));
    }
    
    public static function assignItemsUsingMatchAll($feedId)
    {
        //insert select
        $sql = 
            "INSERT
				INTO relFeedsContents
				SELECT %d AS feedREL, contentREL
                    FROM 
                    (
                        SELECT contentREL, COUNT(*) AS nrOfTags
                            FROM relContentsTags
                            WHERE tagREL IN
                            (
                                SELECT tagREL 
                                FROM relFeedsTags	
                                WHERE feedREL = %d
                            )
                            GROUP BY contentREL
                    ) AS derived
                    WHERE derived.nrOfTags = 
                    (
                        SELECT COUNT(*) 
                        FROM relFeedsTags
                        WHERE feedREL = %d
                    )";
        BQuery::Database()->queryExecute(sprintf($sql, $feedId, $feedId, $feedId));
    }
    
    public static function assignItemsUsingMatchNone($feedId)
    {
        //insert select
        $sql = 
            "INSERT 
				INTO relFeedsContents
				SELECT DISTINCT %d AS feedREL, contentID AS contentREL 
					FROM Contents LEFT 
					JOIN relContentsTags ON (Contents.contentID = relContentsTags.contentREL)
					WHERE 
    					ISNULL(tagREL) 
    					OR tagREL NOT IN
    					(
    						SELECT tagREL 
    							FROM relFeedsTags
    							WHERE feedREL = %d
    					)";
        BQuery::Database()->queryExecute(sprintf($sql, $feedId, $feedId));
    }
}
?>