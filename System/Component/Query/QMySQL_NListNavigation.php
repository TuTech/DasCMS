<?php
class QMySQL_NListNavigation extends BQuery 
{
    public static function listTagged($tags)
    {
        $DB = parent::Database();
        $tagfilter = '';
        foreach ($tags as $tag) 
		{
			$tagfilter .= 
			    "AND ContentIndex.contentID IN ".    
			        "(SELECT relContentTags.contentREL ".
			            "FROM relContentTags LEFT JOIN Tags ON (Tags.tagID = relContentTags.tagREL) ".
		                "WHERE Tags.tag = '".$DB->escape($tag)."') ";
		}
		$sql = "SELECT Aliases.alias, ContentIndex.title, Managers.manager, ContentIndex.pubDate AS PubDate ".
    				"FROM Aliases ".
    				"LEFT JOIN ContentIndex ON (ContentIndex.contentID = Aliases.contentREL)".
    				"LEFT JOIN Managers ON (ContentIndex.managerREL = Managers.managerID) ".
    				"WHERE Aliases.active = 1 ".
    				"AND PubDate > 0 ".
    				"AND PubDate <= ".time().
    				$tagfilter.
    				" ORDER BY Title ASC";
		return $DB->query($sql, DSQL::NUM);
    }
}
?>