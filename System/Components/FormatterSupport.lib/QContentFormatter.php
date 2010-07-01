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
    public static function getFormatterName($cid, $forClass = null)
    {
        $sql = "SELECT name 
        			FROM Formatters 
        			LEFT JOIN relContentsFormatters ON (formatterID = formatterREL) 
        			WHERE 
						contentREL = %d%s";
        $sql = sprintf($sql, $cid, self::classFilter($forClass));
        return BQuery::Database()->query($sql, DSQL::NUM);
    }

	protected static function classFilter($forClass){
		$ret = '';
		if($forClass == null){
			$ret = ' AND ISNULL(classREL)';
		}
		else{
			$ret = sprintf(" AND classREL = (SELECT classID FROM Classes WHERE class = '%s')", BQuery::Database()->escape($forClass));
		}
		return $ret;
	}

    public static function setFormatter($cid, $formattername, $forClass = null)
    {
        $DB = BQuery::Database();
        $DB->beginTransaction();
        $DB->queryExecute(sprintf('DELETE FROM relContentsFormatters WHERE contentREL = %d%s', $cid, self::classFilter($forClass)));
        $res = $DB->queryExecute(sprintf(
        	"INSERT INTO relContentsFormatters 
        		SELECT 
					%d AS contentREL,
					formatterID AS formatterREL,
					%s AS classREL
        		FROM Formatters 
        		WHERE Formatters.name = '%s'", 
				$cid,
				($forClass == null ? 'NULL' : sprintf("(SELECT classID FROM Classes WHERE class = '%s')", $DB->escape($forClass))),
				$DB->escape($formattername)));
        $DB->commit();
        return $res;
    }
	
	public static function removeFormatter($cid, $forClass = null)
    {
        $DB = BQuery::Database();
        $DB->queryExecute(sprintf('DELETE FROM relContentsFormatters WHERE contentREL = %d%s', $cid, self::classFilter($forClass)));
    }


}
?>