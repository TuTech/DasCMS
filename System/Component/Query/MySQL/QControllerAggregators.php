<?php
class QControllerAggregators extends BQuery
{
    /**
     * @return DSQLResult
     */
    public static function getList()
    {
        $sql = "SELECT name FROM ContentAggregators ORDER BY name";
        return BQuery::Database()->query($sql, DSQL::NUM);
    }
    
    /**
     * @return DSQLResult
     */
    public static function getListOfOutdated()
    {
        $sql = "SELECT DISTINCT name 
        			FROM ReaggregateContents 
        			LEFT JOIN ContentAggregators ON (contentAggregatorREL = contentAggregatorID)
        			ORDER BY name";
        return BQuery::Database()->query($sql, DSQL::NUM);
    }
    
    /**
     * @param string $className
     * @return DSQLResult
     */
    public static function getClassRef($className)
    {
        $sql = "SELECT classID FROM Classes WHERE class = '%s'";
        $DB = BQuery::Database();
        $res = $DB->query(sprintf($sql, $DB->escape($className)), DSQL::NUM);
        if($res->getRowCount() == 0)
        {
            $res->free();
            $sql = "INSERT INTO Classes (class) VALUES ('%s')";
            $DB->queryExecute(sprintf($sql, $DB->escape($className)));
            $res = $DB->query('SELECT LAST_INSERT_ID()', DSQL::NUM);
        }
        return $res;
    }
    
    /**
     * @param int $classID
     * @param string $name
     * @param string$data
     * @return int inserted rows (should be 1)
     */
    public static function insertAggregator($classID,  $name, $data)
    {
        $DB = BQuery::Database();
        $sql = "INSERT INTO 
        			ContentAggregators (name, aggregatorClassREL, aggregatorData) 
        			VALUES ('%s', %d, '%s')";
        return $DB->queryExecute(sprintf($sql, $DB->escape($name),$classID,$DB->escape($data)));
    }    
    /**
     * @param int $classID
     * @param string $name
     * @param string$data
     * @return int inserted rows (should be 1)
     */
    public static function updateAggregator($classID,  $name, $data)
    {
        $DB = BQuery::Database();
        $sql = "UPDATE ContentAggregators 
        			SET aggregatorData = '%s'
        			WHERE name = '%s'
        				AND aggregatorClassREL = %d";
        return $DB->queryExecute(sprintf($sql, $DB->escape($data),$DB->escape($name),$classID));
    }
    
    /**
     * @param string$name
     * @return DSQLResult
     */
    public static function loadAggregator($name)
    {
        $DB = BQuery::Database();
        $sql = "SELECT Classes.class, ContentAggregators.contentAggregatorID, ContentAggregators.aggregatorData
        			FROM ContentAggregators 
        			LEFT JOIN Classes ON (aggregatorClassREL = classID)
        			WHERE name = '%s'";
        return $DB->query(sprintf($sql, $DB->escape($name)), DSQL::NUM);
    }
    
    public static function reaggregateAggregator($aid)
    {
        $DB = BQuery::Database();
        $sql = "INSERT INTO ReaggregateContents 
        			SELECT %d AS 'contentAggregatorREL', Contents.contentID AS 'contentREL'
        				FROM Contents 
        				WHERE Contents.contentID NOT IN 
        					(SELECT contentREL AS 'contentID' FROM ReaggregateContents WHERE contentAggregatorREL = %d)";
        return $DB->queryExecute(sprintf($sql, $aid,$aid));
    }
    
    public static function reaggregateContent($cid)
    {
        $DB = BQuery::Database();
        $sql = "INSERT INTO ReaggregateContents 
        			SELECT ContentAggregators.contentAggregatorID AS 'contentAggregatorREL', %d AS 'contentREL'
        				FROM ContentAggregators 
        				WHERE ContentAggregators.contentAggregatorID NOT IN 
        					(SELECT contentAggregatorREL AS 'contentAggregatorID' FROM ReaggregateContents WHERE contentREL = %d)";
        return $DB->queryExecute(sprintf($sql, $cid, $cid));
    }
}
?>