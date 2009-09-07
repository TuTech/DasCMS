<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2009-09-03
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage Query-MySQL
 */
class QContentFormatter extends BQuery 
{
    /**
     * result has exactly one row
     * @param string $name
     * @return DSQLResult 
     */
    public static function getFormatterName($cid)
    {
        $sql = "SELECT name 
        			FROM Formatters 
        			LEFT JOIN relContentsFormatters ON (formatterID = formatterREL) 
        			WHERE contentREL = %d";
        $sql = sprintf($sql, $cid);
        return BQuery::Database()->query($sql, DSQL::NUM);
    }
        
    public static function setFormatter($cid, $formattername)
    {
        $DB = BQuery::Database();
        $DB->beginTransaction();
        $DB->queryExecute(sprintf('DELETE FROM relContentsFormatters WHERE contentREL = %d', $cid));
        $res = $DB->queryExecute(sprintf(
        	"INSERT INTO relContentsFormatters 
        		SELECT %d as contentREL, formatterID as formatterREL 
        		FROM Formatters 
        		WHERE Formatter.name = '%s'", $cid, $DB->escape($formattername)));
        $DB->commit();
        return $res;
    }
}
?>