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
class QFormatterContainer extends BQuery 
{
    /**
     * result has exactly one row
     * @param string $name
     * @return DSQLResult 
     */
    public static function getFormatter($name)
    {
        $sql = "SELECT formatterData FROM Formatters WHERE name = '%s'";
        $DB = BQuery::Database();
        $sql = sprintf($sql, $DB->escape($name));
        $res = $DB->query($sql, DSQL::NUM);
        if($res->getRowCount() != 1)
        {
            throw new XUndefinedIndexException('formatter not found');
        }
        return $res;
    }
        
    public static function setFormatter($name, $data)
    {
        $sql = "INSERT INTO Formatters (name, formatterData) 
        			VALUES ('%s', '%s')
        			ON DUPLICATE KEY UPDATE formatterData = '%s'";
        $DB = BQuery::Database();
        $data = $DB->escape($data);
        $sql = sprintf($sql, $DB->escape($name), $data, $data);
        return $DB->queryExecute($sql);
    }
        
    public static function listFormatters()
    {
        $sql = "SELECT name FROM Formatters";
        return BQuery::Database()->query($sql, DSQL::NUM);
    }
        
    public static function deleteFormatter($name)
    {
        $sql = "DELETE FROM Formatters WHERE name = '%s'";
        $DB = BQuery::Database();
        $sql = sprintf($sql, $DB->escape($name));
        return $DB->queryExecute($sql);
    }
    
}
?>