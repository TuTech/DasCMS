<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2008-12-03
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage Job
 */
class JImportAtomFeeds implements ISchedulerJob
{
    private $message = 'OK';
    private $code = 0;
    
    /**
     * offset for next run
     * @return int seconds
     */
    public function getInterval()
    {
        return 20*ISchedulerJob::SECOND;
    }
    
    /**
     * @return void
     */
    public function run()
    {
        $stat = 'ok';
        $DB = DSQL::getSharedInstance();
        $DB->beginTransaction();
        $res = QJImportAtomFeeds::getNextFeedURL();
        $url = null;
        if($res->getRowCount())
        {
            //echo "got rows <br />";
            list($id, $url) = $res->fetch();
            //echo 'feed nr ',$id, ': ', $url, ' found <br />';
            $res->free();
            QJImportAtomFeeds::getUpdateFetchDate($id);
        }
        $DB->commit();
        if($url)
        {
            //echo 'importing <br />';
            $importer = new Import_HTTP_AtomFeed();
            $fail = $importer->import($id, $url);
            //echo $fail, ' failed<br />';
            $stat = $fail ? 'stopped' : 'new';
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

	public function getEnd()
    {
        return null;
    }
}
?>