<?php
class QImportIMAPMailBox extends BQuery 
{
    /**
     * Result contains:
     * connectionString, username, password, updated, status
     * 
     * @return DSQLResult
     */
    public static function getDataForID($id)
    {
        //label, server, port, mailBox, user, password
        $DB = BQuery::Database();
        $sql = "SELECT 
                    CONCAT(
                        '{', 
                        MailImportAccounts.server, 
                        ':',     
                        CAST(MailImportAccounts.port AS CHAR), 
                        IF(
                            ISNULL(GROUP_CONCAT(DISTINCT MailImportFlags.flag SEPARATOR '')), 
                                '', 
                                GROUP_CONCAT(DISTINCT MailImportFlags.flag SEPARATOR '')
                            ),
                        '}', 
                        MailImportAccounts.mailBox
                    ), 
                    MailImportAccounts.username,
                    MailImportAccounts.password,
                    MailImportAccounts.updated,
                    MailImportAccounts.status
                FROM MailImportAccounts 
                    LEFT JOIN relMailImportAccountsMailImportFlags ON (mailImportAccountREL = mailImportAccountID)
                    LEFT JOIN MailImportFlags ON (mailImportFlagREL = mailImportFlagID)
                WHERE MailImportAccounts.mailImportAccountID = %d";
        return  $DB->query(sprintf($sql, $id), DSQL::NUM);
    }
    
    /**
     * @return DSQLResult
     */
    public static function getContentsForIds($accountID, array $ids)
    {
        $valid = array();
        foreach ($ids as $id) 
        {
            if(is_numeric($id))
            {
                $valid[] = $id;
            }
        }
        $DB = BQuery::Database();
        $sql = "SELECT MailImportMails.imapID, Aliases.alias
        			FROM MailImportMails 
        				LEFT JOIN Contents ON (MailImportMails.contentREL = Contents.contentID)
        				LEFT JOIN Aliases ON (Contents.primaryAlias = Aliases.aliasID)
    				WHERE mailImportAccountREL = %d AND ";
	    $sql .= (count($valid)) 
	        ? "MailImportMails.imapID IN (%s)" 
	        : '0';
        return  $DB->query(sprintf($sql, $accountID, implode(',', $valid)), DSQL::NUM);
    }
    
    public static function linkMailToContent($accountId, $mailId, $messageId, $sender, $contentId)
    {
        $DB = BQuery::Database();
        $sql = "INSERT INTO mailImportMails (
        				mailImportAccountREL, imapID, messageID, sender,updated,contentREL
    				) VALUES (%d, %d, '%s', '%s', NOW(), %d) 
    				ON DUPLICATE KEY UPDATE						
    					updated = NOW(), contentREL = %d
					";//
         $messageId = $DB->escape($messageId);
         $sender = $DB->escape($sender);
         $sql = sprintf($sql, $accountId, $mailId, $messageId, $sender, $contentId, $contentId);
         $DB->queryExecute($sql);
    }
}
?>