<?php
class QSComponentIndex extends BQuery 
{
    /**
     * @return void
     */
    public static function updateClassIndex($classes)
    {
        $DB = BQuery::Database();
        $sql = 
            "INSERT INTO 
                Classes 
            		(class, guid)
                VALUES 
            		('%s',%s)
                ON DUPLICATE KEY UPDATE 
                    class = '%s',
                    guid = %s";
        $ci = array();
        foreach ($classes as $cname => $cguid) 
        {
            $cn = '"'.$DB->escape($cname).'"';
            $cg = empty($cguid) ? 'NULL' : '"'.$DB->escape($cguid).'"';
        	$DB->queryExecute(sprintf($sql, $cn, $cg, $cn, $cg));
        }
    }
}
?>