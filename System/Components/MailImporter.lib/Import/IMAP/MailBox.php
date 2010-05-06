<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2009-01-16
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage _Import_IMAP
 */
class Import_IMAP_MailBox extends _
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
            $tmp = array_pop(imap_errors());
            throw new Exception($tmp);
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
        	if(preg_match("/^[A-EG-Z\\s]*([1-9][0-9]*)\\)/",$head, $matches))
            //if(preg_match("/^[A-Z\\s]*[NU][A-Z\\s]*([1-9][0-9]*)/",$head, $matches))
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
    
    /**
     * @param CPage $page
     * @param int $withMailId
     * @return Import_IMAP_Mail
     */
    private function updatePage(CPage $page, $withMailId)
    {
        $mail = $this->getMail($withMailId);
        $headParts = explode('#', $mail->getSubject());
        $page->Title = trim(array_shift($headParts));
        $page->Tags = $headParts;
        $page->Content = $mail->getText();
        $page->PubDate = $mail->getDate();
        $page->Save();
        return $mail;
    }
    
    public function getAccounts()
    {
        //...
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
                    $content = Controller_Content::getSharedInstance()->openContent($alias, 'CPage');
                }
                else
                {
                    $content = CPage::Create('mail import '.date('c'));
                }
                $mail = $this->updatePage($content, $mailID);
                QImportIMAPMailBox::linkMailToContent($accountID, $mailID, $accountID.':'.$mailID, $mail->getFrom(), $content->getId());
                $updated[] = $mailID;
            }
            catch (Exception $e)
            {/* this mail will not be flagged - perhaps next import succeeds*/
            throw $e;}
        }
        $this->flagMails($updated, true);
        $this->close();
    }
    
    /**
     * open mail by imap id
     * @param $id
     * @return Import_IMAP_Mail
     */
    private function getMail($id)
    {
        if(empty($id))
        {
            throw new Exception('empty id');
        }
        $nr = imap_msgno($this->connection, $id);
        if(empty($nr))
        {
            throw new Exception('mail not found');
        }
        printf('<p> updating nr %s from %s</p>', $nr, $id);
        $header = imap_header($this->connection, $nr);
        $struct = imap_fetchstructure($this->connection, $nr);
        if($struct->type == 1)
        {
            $body = imap_fetchbody($this->connection, $nr,'2');
        }
        else
        {
            $body = imap_body($this->connection, $nr);
        }
        return new Import_IMAP_Mail(
            $header->from,
            isset($header->to) ? $header->to : null,
            $header->date,
            $header->message_id,
            $header->subject,
            $body
        );
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