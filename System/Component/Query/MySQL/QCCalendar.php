<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2009-07-21
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage Query-MySQL
 */
class QCCalendar extends BQuery
{
    /**
     * @return DSQLResult
     */
    public static function getEvents()
    {
        $sql = "SELECT EventDates.startDate, EventDates.endDate, Aliases.alias FROM EventDates 
        			LEFT JOIN Contents ON (EventDates.contentREL = Contents.contentID)
        			LEFT JOIN Aliases ON (Contents.primaryAlias = Aliases.aliasID)
        			WHERE EventDates.startDate >= CURDATE()
        			  AND Contents.pubDate <= NOW()
        			  AND !ISNULL(Contents.pubDate)
        			ORDER BY EventDates.startDate, EventDates.endDate";
        return BQuery::Database()->query($sql, DSQL::NUM);
    }
}
?>