<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2008-12-03
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage Query-MySQL
 */
class QJImportAtomFeeds extends BQuery 
{
    /**
     * @return DSQLResult
     */
    public static function getNextFeedURL()
    {
        //select for update
        $sql = 
            "SELECT 
					atomSourceID,url
				FROM AtomSources
				WHERE lastFetched < DATE_SUB(NOW(), INTERVAL 10 SECOND)
				ORDER BY lastFetched ASC
				LIMIT 1
				FOR UPDATE";
        return BQuery::Database()->query($sql, DSQL::NUM);
    }
    
    public static function getUpdateFetchDate($id)
    {
        $sql = "UPDATE AtomSources SET lastFetched = NOW() WHERE atomSourceID = %d";
        BQuery::Database()->queryExecute(sprintf($sql, $id));
    }
    
    /**
	 * @return DSQLResult
	 */
    public static function countFeeds()
    {
        $sql = "SELECT COUNT(*) FROM AtomSources";
        return BQuery::Database()->query($sql, DSQL::NUM);
    }
}
?>