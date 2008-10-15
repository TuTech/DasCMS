<?php
class QMySQL_STag extends BQuery 
{
    public static function listTagsOf($mid, $cid)
    {
        $DB = parent::Database();
		$res = $DB->query(sprintf(
		    "SELECT Tags.tag FROM Tags ".
    			"LEFT JOIN relContentTags ON (relContentTags.tagREL = Tags.tagID) ".
    			"LEFT JOIN ContentIndex ON (relContentTags.contentREL = ContentIndex.contentID) ".
    			"LEFT JOIN Managers ON (ContentIndex.managerREL = Managers.managerID) ".
    			"WHERE ContentIndex.managerContentID LIKE '%s' ".
    			    "AND Managers.manager LIKE '%s' ORDER BY Tags.tag;"
			, $DB->escape($cid)
			, $DB->escape($mid)
		), DSQL::ASSOC);
		return $res;
    }
    
    public static function getContentDBID($mid, $cid)
    {
        $DB = parent::Database();
        $res = $DB->query(
            "SELECT ContentIndex.contentID 
                FROM ContentIndex LEFT JOIN Managers
            	ON (ContentIndex.managerREL = Managers.managerID) 
            	WHERE 
            		ContentIndex.managerContentID = '".$DB->escape($contentID)."' 
            		AND Managers.manager = '".$DB->escape($managerId)."' 
            	LIMIT 1",DSQL::NUM);
        return $res;
    }
    
    public static function removeRelationsTo($dbcid)
    {
        $DB = parent::Database();
        $DB->queryExecute("DELETE FROM relContentTags WHERE contentREL = ".$DB->escape($CID));	
    }
    
    public static function dumpNewTags($tags)
    {
        parent::Database()->insert('Tags',array('tag'), $tags, true);
    }
    
    public static function linkTagsTo($tags, $dbcid)
    {
        $DB = parent::Database();
        foreach ($tags as $tag) 
		{
			$DB->insertUnescaped(
				'relContentTags',
				array('contentREL', 'tagREL'),
				array(
					$DB->escape($dbcid),
					"(SELECT tagID FROM Tags WHERE tag = '".$DB->escape($tag)."' LIMIT 1)"
				)
			);
		}
    }
}
?>