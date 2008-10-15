<?php
class QNListNavigation extends BQuery 
{
    /**
     * @param array $tags
     * @return DSQLResult
     */
    public static function listTagged(array $tags)
    {
        return DSQL::call('NListNavigation', 'listTagged', $tags);
    }
}
?>