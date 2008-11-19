<?php
class QJJobJanitor extends BQuery 
{
    /**
     * @return DSQLResult
     */
    public static function getJobList()
    {
        $sql = 
            "SELECT 
					Jobs.jobID, Classes.class, Jobs.start, Jobs.stop
				FROM Jobs
					LEFT JOIN Classes ON (Jobs.classREL = Classes.classID)";
        return BQuery::Database()->query($sql, DSQL::NUM);
    }
    
    public static function removeJobs(array $jobIds)
    {
        if(count($jobIds))
        {
            $ors = array();
            foreach ($jobIds as $id) 
            {
            	$ors[] = sprintf('jobID = %d', $id);
            }
            $sql = "DELETE FROM Jobs WHERE %s";
            return BQuery::Database()->queryExecute(sprintf($sql, implode(' OR ', $ors)));
        }
        return 0;
    }
    
    public static function registerJobClass($class, $guid)
    {
        $DB = BQuery::Database();
        $sql = 
            "INSERT INTO 
                Classes 
            		(class, guid)
                VALUES 
            		('%s',%s)
                ON DUPLICATE KEY UPDATE 
                    class = '%s',
                    guid = %s";
        $cg = empty($guid) ? 'NULL' : '"'.$DB->escape($guid).'"';
    	return $DB->queryExecute(sprintf($sql, $class, $cg, $class, $cg));
    }
    
    public static function addNewJob($jobClass, $interval, $end)
    {
        $sql =     
            "INSERT INTO Jobs
				(classREL, start, stop, rescheduleInterval)
				VALUES
				(
					(SELECT classID FROM Classes WHERE class = '%s'),
					NOW(),
					%s,
					%d
				)";
        $DB = BQuery::Database();
        return $DB->queryExecute(sprintf(
            $sql
            ,$DB->escape($jobClass)
            ,($end != null && is_integer($end)) ? date('"Y-m-d H:i:s"', $end) : 'NULL',
            max(10, $interval)
        ));
    }
    
    public static function scheduleNewJob($job)
    {
        $DB = BQuery::Database();//FIXME sql not that well
        $sql = 
            "INSERT INTO JobSchedules
				(jobREL, scheduled)
				VALUES
				(
					(SELECT 
                        	Jobs.jobID 
                        FROM Jobs 
                        LEFT JOIN Classes ON (Jobs.classREL = Classes.classID) 
                        WHERE class = '%s'),
					NOW()
				)";
        return $DB->queryExecute(sprintf($sql, $DB->escape($job)));
    }
}
?>