<?php
class QSContentIndex extends BQuery 
{
    const TARGET = 'SContentIndex';
    
    /**
     * @param string $alias
     * @return DSQLResult
     */
    public static function getDBID($alias)
    {
        return DSQL::call(self::TARGET, 'getDBID', array($alias));
    }
    
    /**
     * @param array $aliases
     * @return DSQLResult
     */
    public static function getBasicInformation(array $aliases)
    {
        return DSQL::call(self::TARGET, 'getBasicInformation', array($aliases));
    }
    
    /**
     * @param array $aliases
     * @return DSQLResult
     */
    public static function getPrimaryAliases(array $aliases)
    {
        return DSQL::call(self::TARGET, 'getPrimaryAliases', array($aliases));
    }
    
    /**
     * @param string $aliases
     * @return DSQLResult
     */
    public static function getBasicInformationForClass($class)
    {
        return DSQL::call(self::TARGET, 'getBasicInformationForClass', array($class));
    }
    
    /**
     * @param int $$dbid
     * @return DSQLResult
     */
    public static function deleteContent($dbid)
    {
        return DSQL::call(self::TARGET, 'deleteContent', array($dbid));
    }
    
    /**
     * @param string $alias
     * @return DSQLResult
     */
    public static function getMetaInformation($alias)
    {
        return DSQL::call(self::TARGET, 'getMetaInformation', array($alias));
    }
    
    /**
     * @param string $alias
     * @return DSQLResult
     */
    public static function getCreateInformation($alias)
    {
        return DSQL::call(self::TARGET, 'getCreateInformation', array($alias));
    }
    
    /**
     * @param string $alias
     * @return DSQLResult
     */
    public static function getChangeHistory($alias)
    {
        return DSQL::call(self::TARGET, 'getChangeHistory', array($alias));
    }
}
?>