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
class TaskScheduler implements IShareable
{
	private static $instance = null;

	/**
	 * @return TaskScheduler
	 */
	public static function getInstance(){
		if(!self::$instance){
			self::$instance = new TaskScheduler();
		}
		return self::$instance;
	}

	const CLASS_NAME = 'TaskScheduler';
    /**
     * process the next job in line
     */
    public function runJob()
    {
        $ok = true;
		$jobs = Core::Database()
			->createQueryForClass($this)
			->call('count')
			->withoutParameters()
			->fetchSingleValue();
        if($jobs == 0 && class_exists('Task_TaskJanitor', true))
        {
            $job = new Task_TaskJanitor();
            $job->run();
        }  
        else
        {
            $DB = DSQL::getInstance();
            try
            {
				//re-/schedule
				$DB->beginTransaction();
                $res = Core::Database()
					->createQueryForClass($this)
					->call('next')
					->withoutParameters();
				$row = $res->fetchResult();
				$res->free();
				$job = null;
                if($row)
                {
                    list($job, $jobId, $scheduled) = $row;
					Core::Database()
						->createQueryForClass($this)
						->call('start')
						->withParameters($jobId, $scheduled)
						->execute();
					Core::Database()
						->createQueryForClass($this)
						->call('schedule')
						->withParameters($jobId, $jobId)
						->execute();
				}
				$DB->commit();
			}
			catch (Exception $e){
				SErrorAndExceptionHandler::reportException($e);
				if(!empty($jobs)){
					Core::Database()
						->createQueryForClass($this)
						->call('report')
						->withParameters($e->getCode(), 'Scheduler failed: '.$e->getMessage(), $jobId, $scheduled)
						->execute();
				}
				$DB->rollback();
				$job = null;
			}
			//run the job
			if($job != null){
				$DB->beginTransaction();
				try
				{
					$ergStr = '(null)';
					$ergNo = 0;

					if(!class_exists($job, true))
					{
						throw new Exception('Task not found', 1);
					}
					$jobObj = new $job;
					if (!$jobObj instanceof Interface_SchedulerTask)
					{
						throw new Exception('Task not valid', 2);
					}
					$ok = $jobObj->run();
					$ergNo = $jobObj->getStatusCode();
					$ergStr = $jobObj->getStatusMessage();
				}
				catch (Exception $e)
				{
					$DB->rollback();
					$ergNo = $e->getCode();
					$ergStr = $e->getMessage();
					$ok = 'stopped';
				}
				Core::Database()
					->createQueryForClass($this)
					->call('report')
					->withParameters($ergNo, $ergStr, $jobId, $scheduled)
					->execute();
				$DB->commit();
			}
        }
        return $ok;
    }
}
?>