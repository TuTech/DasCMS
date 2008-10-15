<?php
class QMySQL_SAlias extends BQuery 
{
    public static function resolveAlias($alias)
    {
        $DB = parent::Database();
        return $DB->query(
            "SELECT Managers.manager,ContentIndex.managerContentID FROM Aliases ".
				"LEFT JOIN ContentIndex ON (Aliases.contentREL = ContentIndex.contentID) ".
				"LEFT JOIN Managers ON (ContentIndex.managerREL = Managers.managerID) ".
				"WHERE Aliases.alias LIKE '".$DB->escape($alias)."'", 
			DSQL::NUM
		);
    }
}
?>