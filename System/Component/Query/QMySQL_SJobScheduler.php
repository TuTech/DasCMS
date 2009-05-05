<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2008-11-19
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage Query-MySQL
 */
class QSJobScheduler extends BQuery 
{
    /**
     * @return DSQLResult
     */
    public static function hasAnyJobs()
    {
        $sql = "SELECT COUNT(*) FROM Jobs";
        return BQuery::Database()->query($sql, DSQL::NUM);
    }
    
    /**
     * @return DSQLResult
     */
    public static function getNextJob()
    {
        //select for update
        $sql = 
            "SELECT 
					Classes.class, Jobs.jobID, JobSchedules.scheduled
				FROM Jobs
					LEFT JOIN Classes ON (Jobs.classREL = Classes.classID)
					LEFT JOIN JobSchedules on (Jobs.jobID = JobSchedules.jobREL)
				WHERE
					Jobs.start <= NOW()
					AND (Jobs.stop > NOW() OR ISNULL(Jobs.stop))
					AND ISNULL(JobSchedules.started)
					AND JobSchedules.scheduled > 0
					AND JobSchedules.scheduled <= NOW()
				ORDER BY JobSchedules.scheduled ASC
				LIMIT 1
				FOR UPDATE";
        return BQuery::Database()->query($sql, DSQL::NUM);
    }
    
    public static function lockJob($jobID, $scheduledDate)
    {
        $DB = BQuery::Database();
        $sql =
            "SELECT * FROM JobSchedules
				WHERE 
					jobREL = %d
					AND scheduled = '%s'
					FOR UPDATE";
        return $DB->queryExecute(sprintf($sql, $DB->escape($jobId), $DB->escape($scheduledDate)));
    }
    public static function setStarted($jobId, $scheduledDate)
    {
        $DB = BQuery::Database();
        $sql =
            "UPDATE JobSchedules
				SET 
					started = NOW()
				WHERE 
					jobREL = %d
					AND scheduled = '%s'";
        return $DB->queryExecute(sprintf($sql, $DB->escape($jobId), $DB->escape($scheduledDate)));
    }
    
    public static function rescheduleJob($jobId)
    {
        $DB = BQuery::Database();
        $sql = 
            "INSERT INTO JobSchedules
				(jobREL, scheduled)
				VALUES
				(
					%d,
					DATE_ADD(
						NOW(), 
						INTERVAL (SELECT 
										rescheduleInterval 
									FROM Jobs 
									WHERE jobID = %d) 
								SECOND
					)
				)";
        return $DB->queryExecute(sprintf($sql, $DB->escape($jobId), $DB->escape($jobId)));
    }
    
    public static function finishJob($jobId, $scheduledDate, $resultCode, $resultMessage)
    {
        $DB = BQuery::Database();
        $sql =
            "UPDATE JobSchedules
				SET 
					exitCode = %d,
					exitMessage = '%s',
					finished = NOW()
				WHERE 
					jobREL = %d
					AND scheduled = '%s'";
        return $DB->queryExecute(sprintf(
            $sql
            ,$resultCode
            ,$DB->escape($resultMessage)
            ,$DB->escape($jobId)
            ,$DB->escape($scheduledDate)
        ));
    }
}
?>