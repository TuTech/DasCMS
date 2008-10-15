<?php
class QMySQL_WContentLookup extends BQuery 
{
    /**
     * @return DSQLResult
     */
    public static function fetchContentList()
    {
		$sql = 
		    "SELECT ContentIndex.managerContentID, Managers.manager AS Manager,".
				   "ContentIndex.title AS Title, ContentIndex.pubDate ".
				"FROM ContentIndex ".
				"LEFT JOIN Managers ON (ContentIndex.managerREL = Managers.managerID) ".
				"WHERE ContentIndex.pubDate > -1  ORDER BY Manager,Title ASC";
		return parent::Database()->query($sql, DSQL::NUM);
    }
}
?>