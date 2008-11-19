<?php
class SJobScheduler extends BSystem 
{
    /**
     * process the next job in line
     */
    public static function runJob()
    {
        $ok = true;
        $res = QSJobScheduler::hasAnyJobs();
        list($jobs) = $res->fetch();
        $res->free();
        if($jobs == 0 && class_exists('JJobJanitor'))
        {
            $job = new JJobJanitor();
            $job->run();
        }  
        else
        {
            $DB = DSQL::alloc()->init();
            $DB->beginTransaction();
            $res = QSJobScheduler::getNextJob();
            if($res->getRowCount() == 1)
            {
                list($job, $jobId, $scheduled) = $res->fetch();
                $res->free();
                QSJobScheduler::setStarted($jobId, $scheduled);
                QSJobScheduler::rescheduleJob($jobId);
                $DB->commit();
                $DB->beginTransaction();
                $ergStr = '(null)';
                $ergNo = 0;
                try
                {
                    if(!class_exists($job, true))
                    {
                        throw new Exception('Job not found', 1);
                    }
                    $jobObj = new $job;
                    if (!$jobObj instanceof BJob) 
                    {
                    	throw new Exception('Job not a valid job', 2);
                    }
                    $jobObj->run();
                    $ergNo = $jobObj->getStatusCode();
                    $ergStr = $jobObj->getStatusMessage();
                }
                catch (Exception $e)
                {
                    $ergNo = $e->getCode();
                    $ergStr = $e->getMessage();
                    $ok = false;
                }
                QSJobScheduler::finishJob($jobId,$scheduled,$ergNo, $ergStr);
            }
            $DB->commit();
        }
        return $ok;
    }
}
?>