<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2009-04-22
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage Query-MySQL
 */
class QSSearchIndexer extends BQuery 
{
    /**
     * @return DSQLResult
     */
    public static function getAttributes()
    {
        $sql = "SELECT 
						searchAttributeWeightID, attribute
					FROM SearchAttributeWeights";
        return BQuery::Database()->query($sql, DSQL::NUM);
    }
        
    /**
     * @return DSQLResult
     */
    public static function getNetToUpdate()
    {
        $sql = "SELECT 
						Aliases.alias
					FROM SearchIndexOutdated
					LEFT JOIN Aliases USING (contentREL)
					ORDER BY since ASC
					LIMIT 1";
        return BQuery::Database()->query($sql, DSQL::NUM);
    }
    
    public static function scheduleUpdate($contentID)
    {
        $sql = sprintf("INSERT IGNORE INTO SearchIndexOutdated (contentREL) VALUES (%d)", $contentID);
        return BQuery::Database()->queryExecute($sql);
    }
    
    /**
     * insert unknown features 
     * @param $features
     */
    public static function dumpFeatures(array $features)
    {
        
        $sql = "INSERT INTO SearchFeatures (searchFeature) VALUES ";
        $DB = BQuery::Database();
        $values = array();
        foreach ($features as $f)
        {
           $values[$f] = '("'.$DB->escape($f).'") ON DUPLICATE KEY UPDATE searchFeature = "'.$DB->escape($f).'"';
           $DB->queryExecute($sql.$values[$f]);
           echo $sql.$values[$f].'<br />';
        }
        if(count($values))
        {
            //$sql .= implode(',', $values);
            //echo $sql;
            //$DB->queryExecute($sql);
        }
    }  
      
    public static function removeIndex($contentID)
    {
        $sql = sprintf("DELETE FROM SearchIndex WHERE contentREL = %d", $contentID);
        return BQuery::Database()->queryExecute($sql);
    }      
    
    public static function removePendingUpdate($contentID)
    {
        $sql = sprintf("DELETE FROM SearchIndexOutdated WHERE contentREL = %d", $contentID);
        BQuery::Database()->queryExecute($sql);
    }
    
    public static function linkContentAttributeFeatures($contentID, $attributeID, array $features)
    {
        $DB = BQuery::Database();
        foreach ($features as $f => $count)
        {
            $sql = "INSERT INTO SearchIndex (contentREL, searchAttributeWeightREL, searchFeatureREL, featureCount)VALUES \n";
            $sql .= sprintf('(%d, %d, (SELECT searchFeatureID FROM SearchFeatures WHERE searchFeature = "%s"), %d)'
            	,$contentID
            	,$attributeID
            	,$DB->escape($f)
            	,$count
            );
            $DB->queryExecute($sql);
        }
    }
}
?>