<?php
class QImportHTTPAtomFeed extends BQuery 
{
    /**
     * @return DSQLResult
     */
    public static function updateMatches($guid, $timestamp)
    {
        $DB = BQuery::Database();
        $sql = "SELECT COUNT(*) FROM AtomImports WHERE guid = '%s' AND lastUpdate = '%s'";
        return  $DB->query(sprintf($sql, $DB->escape($guid), $DB->escape(date('Y-m-d H:i:s', $timestamp))), DSQL::NUM);
    }
    
    /**
     * @return DSQLResult
     */
    public static function getContentAlias($guid)
    {
        $DB = BQuery::Database();
        $sql = "SELECT Aliases.alias
					 FROM AtomImports 
						LEFT JOIN Aliases USING (contentREL)
					WHERE AtomImports.guid = '%s'
					LIMIT 1";
        return  $DB->query(sprintf($sql, $DB->escape($guid)), DSQL::NUM);
    }
    
    /**
     * @return void
     */
    public static function setImport($source, $guid, $updateTimestamp, $contentId)
    {
        $DB = BQuery::Database();
        $sql = "INSERT INTO AtomImports 
					(atomSourceREL, guid, lastUpdate, contentREL) 
					VALUES 
					(%d, '%s', '%s', %d)
					ON DUPLICATE KEY UPDATE 
						lastUpdate = '%s'";
        $DB->queryExecute(sprintf(
            $sql,
            $source,
            $DB->escape($guid),
            $DB->escape(date('Y-m-d H:i:s', $updateTimestamp)),
            $contentId,
            $DB->escape(date('Y-m-d H:i:s', $updateTimestamp))
        ));
    }
}
?>