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
			$res->free();
            throw new XUndefinedIndexException('formatter not found');
        }
        return $res;
    }
	/**
     * result has exactly one row
     * @param string $name
     * @return DSQLResult
     */
    public static function isFormatter($name)
    {
        $sql = "SELECT name FROM Formatters WHERE name = '%s'";
        $DB = BQuery::Database();
        $sql = sprintf($sql, $DB->escape($name));
        $res = $DB->query($sql, DSQL::NUM);
        $count = $res->getRowCount();
		$res->free();
        return $count;
    }
        
    public static function setFormatter($name, $data)
    {
		if(QFormatterContainer::isFormatter($name)){
			$sql = "UPDATE Formatters SET formatterData = '%s' WHERE name = '%s'";
		}
		else{
			$sql = "INSERT INTO Formatters (formatterData, name) VALUES ('%s', '%s')";
		}

        $DB = BQuery::Database();
        $sql = sprintf($sql, $DB->escape($data), $DB->escape($name));
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