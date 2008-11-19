<?php
class JTruncateJobLog extends BJob 
{
    private $message = 'OK';
    private $code = 0;
    
    /**
     * offset for next run
     * @return int seconds
     */
    public function getInterval()
    {
        return BJob::DAY;
    }
    
    /**
     * @return void
     */
    public function run()
    {
        $this->message = sprintf('%d job schedules removed', QJTruncateJobLog::removeJobs());
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