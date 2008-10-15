<?php
class QWContentLookup extends BQuery 
{
    /**
     * @param array $tags
     * @return DSQLResult
     */
    public static function fetchContentList()
    {
        return DSQL::call('WContentLookup', 'fetchContentList');
    }
}
?>