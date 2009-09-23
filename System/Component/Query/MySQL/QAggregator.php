<?php
class QAggregator extends BQuery
{
    /**
     * @param string $table
     * @param int $aggregatorId
     * @return DSQLResult
     */
    public static function countAssignedContents($table, $aggregatorId)
    {
        $sql = "SELECT COUNT(*) FROM %s WHERE contentAggregatorREL = %d";
        $DB = BQuery::Database();
        return $DB->query(sprintf($sql, $DB->escape($table), $aggregatorId), DSQL::NUM);
    }
}
?>