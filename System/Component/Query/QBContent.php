<?php
class QBContent extends BQuery 
{
    public static function getBasicMetaData($alias)
    {
        return DSQL::call('BContent', 'getBasicMetaData', array($alias));
    }
    
    public static function getAdditionalMetaData($alias)
    {
        return DSQL::call('BContent', 'getAdditionalMetaData', array($alias));
    }
    
    public static function getClass($alias)
    {
        return DSQL::call('BContent', 'getClass', array($alias));
    }
    
    public static function saveMetaData($id, $title, $pubDate, $description, $size)
    {
        return DSQL::call('BContent', 'saveMetaData', array($id, $title, $pubDate, $description, $size));
    }
}
?>