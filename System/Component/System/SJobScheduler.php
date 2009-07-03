<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2008-11-19
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage System
 */
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
        if($jobs == 0 && class_exists('JJobJanitor', true))
        {
            $job = new JJobJanitor();
            $job->run();
        }  
        else
        {
            $DB = DSQL::getSharedInstance();
            $DB->beginTransaction();
            try
            {
                $res = QSJobScheduler::getNextJob();
                if($res->getRowCount() == 1)
                {
                    list($job, $jobId, $scheduled) = $res->fetch();
                    $res->free();
                    $res = QSJobScheduler::lockJob($jobId, $scheduled);
                    if($res == 0)
                    {
                        return 'conflict';
                    }
                    QSJobScheduler::setStarted($jobId, $scheduled);
                    QSJobScheduler::rescheduleJob($jobId);
                    $DB->commit();
                    $DB->beginTransaction();
                    $ergStr = '(null)';
                    $ergNo = 0;
                    
                        if(!class_exists($job, true))
                        {
                            throw new Exception('Job not found', 1);
                        }
                        $jobObj = new $job;
                        if (!$jobObj instanceof BJob) 
                        {
                        	throw new Exception('Job not a valid job', 2);
                        }
                        $ok = $jobObj->run();
                        $ergNo = $jobObj->getStatusCode();
                        $ergStr = $jobObj->getStatusMessage();
                    
                    QSJobScheduler::finishJob($jobId,$scheduled,$ergNo, $ergStr);
                }
                $DB->commit();
            }
            catch (XDatabaseException $e)
            {
                $e->rollback();
                $ergNo = $e->getCode();
                $ergStr = $e->getMessage();
                $ok = 'fail';
            }
            catch (Exception $e)
            {
                $ergNo = $e->getCode();
                $ergStr = $e->getMessage();
                $ok = 'stopped';
            }
        }
        return $ok;
    }
}
?>