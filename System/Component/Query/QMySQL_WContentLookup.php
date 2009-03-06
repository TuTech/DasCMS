<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2008-10-15
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage Query-MySQL
 */
class QWContentLookup extends BQuery 
{
    /**
     * @return DSQLResult
     */
    public static function fetchContentList()
    {
		$sql = "
            SELECT 
            		Classes.class,
            		Aliases.alias,
            		Contents.title,
            		Contents.pubDate
            	FROM Contents
            	LEFT JOIN Aliases ON (Contents.primaryAlias = Aliases.aliasID)
            	LEFT JOIN Classes ON (Contents.type = Classes.classID)
            	ORDER BY Classes.class, Contents.title ASC";
		return BQuery::Database()->query($sql, DSQL::NUM);
    }
    
}
?>