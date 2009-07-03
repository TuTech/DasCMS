<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2009-01-13
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage Query-MySQL
 */
class QPAuthentication extends BQuery 
{
    /**
     * log login attempt
     * @param int $ipadr
     * @param string $user
     * @param boolean $success
     * @return void
     */
    public static function logAccess($ipadr, $user, $success)
    {
        $DB = BQuery::Database();
        $sql = "INSERT INTO AuthorisationLog (IPAdr, UserName, Status) VALUES (%d, '%s', '%s')";
        $user = $DB->escape($user);
        $sql = sprintf($sql, $ipadr, $user, $success ? 'SUCCESS' : 'FAIL');
        $DB->queryExecute($sql);
    }

    /**
     * get the number of failed login attempts with this user + ip-address combination
     * @param int $ipadr
     * @param string $user
     * @return DSQLResult
     */
    public static function latestFails($ipadr, $user)
    {
        $DB = BQuery::Database();
        $sql = "SELECT COUNT(*) AS MY_FAILS FROM `AuthorisationLog` WHERE 
                `Status` = 'FAIL' AND
                `IPAdr` = %d AND
                `UserName` = '%s' AND
                `LoginTime` > DATE_SUB(NOW(), INTERVAL 15 MINUTE)";
        $user = $DB->escape($user);
        return $DB->query(sprintf($sql, $ipadr, $user), DSQL::NUM);
    }
    
    /**
     * count the failed login attempts for the given user in the last 15 minutes
     * 
     * @param string $user
     * @return DSQLResult
     */
    public static function countUserFails($user)
    {
        $DB = BQuery::Database();
        $sql = "SELECT COUNT(*) FROM `AuthorisationLog` WHERE 
                `Status` = 'FAIL' AND
                `UserName` = '%s' AND 
                `LoginTime` > DATE_SUB(NOW(), INTERVAL 15 MINUTE)";
        $user = $DB->escape($user);
        return $DB->query(sprintf($sql, $user), DSQL::NUM);
    }
    
    /**
     * count the failed login attempts for the given ip-address in the last 15 minutes
     * 
     * @param int $ipadr
     * @return DSQLResult
     */
    public static function countIPAdrFails($ipadr)
    {
        $DB = BQuery::Database();
        $sql = "SELECT COUNT(*) FROM `AuthorisationLog` WHERE 
                `Status` = 'FAIL' AND
                `IPAdr` = %d AND 
                `LoginTime` > DATE_SUB(NOW(), INTERVAL 15 MINUTE)";
        return $DB->query(sprintf($sql, $ipadr), DSQL::NUM);
    }
}
?>