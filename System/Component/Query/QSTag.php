<?php
class QSTag extends BQuery 
{
    const TARGET = 'STag';
    
    public static function listTagsOf($mid, $cid)
    {
		return DSQL::call(self::TARGET, 'listTagsOf', array($mid, $cid));
    }
    
    public static function getContentDBID($mid, $cid)
    {
        return DSQL::call(self::TARGET, 'getContentDBID', array($mid, $cid));
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