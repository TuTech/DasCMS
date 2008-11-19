<?php
class QWContentLookup extends BQuery 
{
    /**
     * @return DSQLResult
     */
    public static function fetchContentList()
    {
		$sql = "
            SELECT 
            		Classes.class,
            		Aliases.alias,
            		Contents.title,
            		Contents.pubDate
            	FROM Contents
            	LEFT JOIN Aliases ON (Contents.primaryAlias = Aliases.aliasID)
            	LEFT JOIN Classes ON (Contents.type = Classes.classID)
            	ORDER BY Classes.class, Contents.title ASC";
		return BQuery::Database()->query($sql, DSQL::NUM);
    }
    
}
?>