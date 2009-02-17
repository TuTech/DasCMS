<?php
class QSTag extends BQuery 
{
   /**
     * @return DSQLResult
     */
    public static function listTagsOf($alias)
    {
        $DB = BQuery::Database();
		$res = $DB->query(sprintf("
            SELECT Tags.tag 
            	FROM Contents
            	LEFT JOIN relContentsTags ON (Contents.contentID = relContentsTags.contentREL)
            	LEFT JOIN Tags ON (relContentsTags.tagREL = Tags.tagID)
            	LEFT JOIN Aliases ON (Contents.contentID = Aliases.contentREL)
            	WHERE
            		Aliases.alias = '%s'
            	ORDER BY Tags.tag ASC", 
		        $DB->escape($alias)), 
            DSQL::ASSOC);
		return $res;
    }
    
    /**
     * @return DSQLResult
     * @deprecated 
     */
    public static function getContentDBID($alias)
    {
        $DB = BQuery::Database();
        $res = $DB->query(
            "SELECT 
					Contents.contentID 
                FROM Contents 
				LEFT JOIN Aliases ON (Contents.contentID = Aliases.contentREL) 
            	WHERE 
            		Aliases.alias = '".$DB->escape($alias)."'"
			,DSQL::NUM);
        return $res;
    }
    
    /**
     * @return void
     */
    public static function removeRelationsTo($dbcid)
    {
        $DB = BQuery::Database();
        $DB->queryExecute(
            sprintf("DELETE FROM relContentsTags WHERE contentREL = %d",$DB->escape($dbcid))
        );	
    }
    
    /**
     * @return void
     */
    public static function dumpNewTags(array $tags)
    {
        if(count($tags))
        {
            $DB = BQuery::Database();
            $tagData = array();
            foreach ($tags as $tag)
            {
                $tagData[] = '("'.$DB->escape($tag).'")';
            }
            $sql = 'INSERT IGNORE INTO Tags (tag) VALUES '.implode(', ', $tagData);
            $DB->queryExecute($sql);
        }
    }
    /**
     * @return void
     */
    public static function linkTagsTo(array $tags, $dbcid)
    {
        $DB = BQuery::Database();
        foreach ($tags as $tag) 
		{
			$DB->insertUnescaped(
				'relContentsTags',
				array('contentREL', 'tagREL'),
				array(
					$DB->escape($dbcid),
					"(SELECT tagID FROM Tags WHERE tag = '".$DB->escape($tag)."')"
				),
				true
			);
		}
    }
}
?>