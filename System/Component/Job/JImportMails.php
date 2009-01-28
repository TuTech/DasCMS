<?php
class JImportMails extends BJob 
{
    private $message = 'OK';
    private $code = 0;
    
    /**
     * offset for next run
     * @return int seconds
     */
    public function getInterval()
    {
        return 20*BJob::SECOND;
    }
    
    /**
     * @return void
     */
    public function run()
    {
        //begin
        $stat = 'ok';
        try
        {
            //echo 'update for mail account ';
            $account = null;
            $DB = DSQL::alloc()->init();
            $DB->beginTransaction();
            //get account to update + lock
            $res = QJImportMails::getNextMailAccount();
            if($res->getRowCount())
            {
                list($account) = $res->fetch();
                //set account last update
                QJImportMails::updateFetchDate($account);
            }
            $res->free();
            $DB->commit();
            //update account
            if($account != null)
            {
                //echo $account;
                $importer = new Import_IMAP_MailBox();
                $importer->import($account);
                $stat = 'new';
            }//else echo ' not available';
        }
        catch (Exception $e)
        {
//            printf(
//            	'<h1>%s (%d)</h1><h2>%s@%d</h2><p>%s</p><p><pre>%s</pre></p>',
//            	get_class($e),
//            	$e->getCode(),
//            	$e->getFile(),
//            	$e->getLine(),
//            	$e->getMessage(),
//            	$e->getTraceAsString()
//            );
            //report errors
            $this->message = $e->getMessage();
            $this->code = $e->getCode();
            $stat = 'stopped';
        }
        return $stat;
    }
    
    /**
     * get status text for the processed result
     * @return string (max length 64)
     */
    public function getStatusMessage()
    {
        return $this->message;
    }
    
    /**
     * get status code
     * @return int status const
     */
    public function getStatusCode()
    {
        return $this->code;
    }    
}
?>