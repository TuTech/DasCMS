<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2009-01-21
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage Query-MySQL
 */
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