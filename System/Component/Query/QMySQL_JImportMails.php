<?php
class QJImportMails extends BQuery 
{
    /**
     * @return DSQLResult
     */
    public static function getNextMailAccount()
    {
        //select for update
        $sql = 
            "SELECT 
					mailImportAccountID
				FROM MailImportAccounts
				WHERE 
					updated < DATE_SUB(NOW(), INTERVAL 10 SECOND)
					AND
					status = 'ENABLED'
				ORDER BY updated ASC
				LIMIT 1
				FOR UPDATE";
        return BQuery::Database()->query($sql, DSQL::NUM);
    }
    
    public static function updateFetchDate($id)
    {
        $sql = "UPDATE MailImportAccounts 
        	SET updated = NOW() 
        	WHERE mailImportAccountID = %d";
        BQuery::Database()->queryExecute(sprintf($sql, $id));
    }
}
?>