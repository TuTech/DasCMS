<?php
class QSAlias extends BQuery 
{
    const TARGET = 'SAlias';
    
    /**
     * @param string $alias
     * @return void
     */
    public static function setActive($alias)
    {
        return DSQL::call(self::TARGET, 'setActive', array($alias));
    }
    
    /**
     * @param string $alias
     * @return DSQLResult
     */
    public static function resolveAlias($alias)
    {
        return DSQL::call(self::TARGET, 'resolveAlias', array($alias));
    }
    
    /**
     * @param string $aliasA
     * @param string $aliasB
     * @return DSQLResult
     */
    public static function match($aliasA, $aliasB)
    {
        return DSQL::call(self::TARGET, 'match', array($aliasA, $aliasB));
    }

    /**
     * @param string $alias
     * @return DSQLResult
     */
    public static function reloveAliasToID($alias)
    {
        return DSQL::call(self::TARGET, 'reloveAliasToID', array($alias));
    }
   /**
     * @param int $dbid
     * @param string $newAlias
     * @return boolean success
     */
    public static function insertAndCheckAlias($dbid, $newAlias)
    {
        return DSQL::call(self::TARGET, 'insertAndCheckAlias', array($dbid, $newAlias));
    }
    
    /**
     * @param string $someAlias
     * @return string|null
     */
    public static function getPrimaryAlias($someAlias)
    {
        return DSQL::call(self::TARGET, 'getPrimaryAlias', array($someAlias));
    }
    
}
?>