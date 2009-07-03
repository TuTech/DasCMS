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
class QJTruncateJobLog extends BQuery 
{
    public static function removeJobs()
    {
        $sql = "DELETE FROM JobSchedules WHERE started <= DATE_SUB(NOW(), INTERVAL 1 WEEK)";
        return BQuery::Database()->queryExecute($sql);
    }
}
?>