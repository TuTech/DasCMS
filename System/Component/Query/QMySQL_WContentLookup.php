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
    public static function fetchContentList($mode, $filter, $page, $itemsPerPage, array $mimetypes = array())
    {
        $DB = BQuery::Database();
		$sql = "SELECT 
            		Classes.class,
            		Aliases.alias,
            		Contents.title,
            		Contents.pubDate
            	FROM Contents
            	LEFT JOIN Aliases ON (Contents.primaryAlias = Aliases.aliasID)
            	LEFT JOIN Classes ON (Contents.type = Classes.classID) ".
            	(count($mimetypes) == 0 ? '' : ' LEFT JOIN Mimetypes ON (Contents.mimetypeREL = Mimetypes.mimetypeID) ').
            	"WHERE ";
		$sql .= ' Contents.title LIKE "%'.$DB->escape($filter).'%" ';
		if(count($mimetypes) > 0)
		{
		    foreach ($mimetypes as $k => $v)
		    {
		        $mimetypes[$k] = $DB->escape($v);
		    }
		    $sql .= ' AND (Mimetypes.mimetype LIKE "'.implode('" OR Mimetypes.mimetype LIKE "', $mimetypes).'") ';
		}
		switch($mode)
		{
		    case 'priv':
		        $sql .= ' AND Contents.pubDate = "0000-00-00 00:00:00" ';break;
		    case 'sched':
		        $sql .= ' AND Contents.pubDate > NOW() ';break;
		    case 'pub':
		        $sql .= ' AND Contents.pubDate > "0000-00-00 00:00:00" AND Contents.pubDate < NOW() ';break;
		}
        $sql .= sprintf("ORDER BY Contents.title ASC LIMIT %d OFFSET %d", $itemsPerPage+1, $itemsPerPage - $page * $itemsPerPage);
		return $DB->query($sql, DSQL::NUM);
    }
}
?>