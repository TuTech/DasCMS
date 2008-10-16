<?php
class QSTag extends BQuery 
{
    const TARGET = 'STag';
    
    public static function listTagsOf($alias)
    {
		return DSQL::call(self::TARGET, 'listTagsOf', array($alias));
    }
    
    public static function getContentDBID($alias)
    {
        return DSQL::call(self::TARGET, 'getContentDBID', array($alias));
    }
    
    public static function removeRelationsTo($dbcid)
    {
        return DSQL::call(self::TARGET, 'removeRelationsTo', array($dbcid));	
    }
    
    public static function dumpNewTags(array $tags)
    {
        return DSQL::call(self::TARGET, 'dumpNewTags', array($tags));	
    }
    
    public static function linkTagsTo(array $tags, $dbcid)
    {
        DSQL::call(self::TARGET, 'linkTagsTo', array($tags, $dbcid));	
    }
}
?>