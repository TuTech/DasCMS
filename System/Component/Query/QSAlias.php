<?php
class QSAlias extends BQuery 
{
    const TARGET = 'SAlias';
    /**
     * @param string $alias
     * @return DSQLResult
     */
    public static function resolveAlias($alias)
    {
        return DSQL::call(self::TARGET, 'resolveAlias', array($alias));
    }
}
?>