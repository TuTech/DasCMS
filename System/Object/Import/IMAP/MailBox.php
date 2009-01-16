<?php
class Import_IMAP_MailBox extends _Import_IMAP
{
    private $errors = array();
    private $connection = null;
    private $contentMap = array();
        
    public function __construct()
    {
    }
    
    /**
     * @param string $connectionString
     * @param string $username
     * @param string $password
     * @return void
     * @throws Exception
     */
    private function connect($connectionString, $username, $password)
    {
        $this->connection = @imap_open($connectionString, $username, $password);
        if(!$this->connection)
        {
            throw new Exception(array_pop(imap_errors()));
        }
    }
    
    /**
     * @return void
     */
    private function close()
    {
        if($this->connection)
        {
            $this->errors = imap_errors();
            @imap_close($this->connection);
        }
    }
    
    /**
     * @return array <p>nr => imap_id</p>
     */
    private function fetchUnread()
    {
        $hdrs = imap_headers($this->connection);
        $numbers = array();
        foreach ($hdrs as $head) 
        {
            //New or Unread
            if(preg_match("/^[A-Z\\s]*[NU][A-Z\\s]*([1-9][0-9]*)/",$head, $matches))
        	{
        	    $numbers[$matches[1]] = imap_uid($this->connection, $matches[1]);
        	}
        }
        return $numbers;
    }
    
	/**
     * @return array <p>nr => imap_id</p>
     */
    private function fetchUnflagged()
    {
        $hdrs = imap_headers($this->connection);
        $numbers = array();
        foreach ($hdrs as $head) 
        {
            //not having F set
            if(preg_match("/^[A-EG-Z\\s]*([1-9][0-9]*)\\)/",$head, $matches))
        	{
        	    $numbers[$matches[1]] = imap_uid($this->connection, $matches[1]);
        	}
        }
        return $numbers;
    }
    
    private function loadContentMap($accountID, array $mailIds)
    {
        $res = QImportIMAPMailBox::getContentsForIds($accountID, $mailIds);
        //mark all as new
        foreach ($mailIds as $id) 
        {
        	$this->contentMap[$id] = null;
        }
        //precess input
        while($row = $res->fetch())
        {
            list($id, $alias) = $row;
            $this->contentMap[$id] = $alias;
        }
    }
    
    private function flagMails(array $mails, $useIds = false)
    {
        if(count($mails))
        {
            $opt = ($useIds) ? ST_UID : null;
            $seq = implode(',', $mails);
            imap_setflag_full($this->connection, $seq, "\\Flagged", $opt);
        }    
    }
    
    private function updatePage(CPage $page, $withMailId)
    {
        throw new Exception('not implemented');
        //load mail
        //set pubdate
        //set tags
        //set title
        //set content
        //$page->Save();
    }
    
    /**
     * <p>import mails from account</p>
     * @param int $accountID
     * @return void
     * @throws XUndefinedException
     * @throws XPermissionDeniedException
     * @throws Exception
     */
    public function import($accountID)
    {    
         $this->errors = array();
         $this->connection = null;
         $this->contentMap = array();
        //get account login data //connectionString, username, password, updated, status
        $res = QImportIMAPMailBox::getDataForID($accountID);
        if($res->getRowCount() != 1)
        {
            throw new XUndefinedException('account not found');
        } 
        list($connectionString, $username, $password, $updated, $status) = $res->fetch();
        if($status == 'DISABLED')
        {
            throw new XPermissionDeniedException('account disabled');
        }
        $this->connect($connectionString, $username, $password);
        $unread = $this->fetchUnread();//nr => id
        $this->loadContentMap($accountID, array_values($unread));
        $updated = array();
        foreach($this->contentMap as $mailID => $alias)
        {
            try
            {
                if(!empty($alias))
                {
                    $content = CPage::Open($alias);
                }
                else
                {
                    $content = CPage::Create('mail import '.date('c'));
                }
                $this->updatePage($content, $mailID);
                QImportIMAPMailBox::linkMailToContent($mailID, $content->getId());
                $updated[] = $mailID;
            }
            catch (Exception $e)
            {/* this mail will not be flagged - perhaps next import succeeds*/}
        }
        $this->flagMails($updated, true);
        $this->close();
    }
    
    /**
     * errors from the imap connection
     * @return array
     */
    public function getIMAPErrors()
    {
        return $this->errors;
    }
}
?>