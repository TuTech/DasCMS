<?php
class JImportAtomFeeds extends BJob 
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
        echo 'hello!<br />';
        $DB = DSQL::alloc()->init();
        $DB->beginTransaction();
        $res = QJImportAtomFeeds::getNextFeedURL();
        $url = null;
        if($res->getRowCount())
        {
            echo "got rows <br />";
            list($id, $url) = $res->fetch();
            echo 'feed nr ',$id, ': ', $url, ' found <br />';
            $res->free();
            QJImportAtomFeeds::getUpdateFetchDate($id);
        }
        $DB->commit();
        if($url)
        {
            echo 'importing <br />';
            $importer = new Import_HTTP_AtomFeed();
            $fail = $importer->import($id, $url);
            echo $fail, ' failed<br />';
        }
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