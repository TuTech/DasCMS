<?php
class QJTruncateJobLog extends BQuery 
{
    public static function removeJobs()
    {
        $sql = "DELETE FROM JobSchedules WHERE started <= DATE_SUB(NOW(), INTERVAL 1 WEEK)";
        return BQuery::Database()->queryExecute($sql);
    }
}
?>