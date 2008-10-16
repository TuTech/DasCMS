<?php
class QSComponentIndex extends BQuery 
{
    public static function updateClassIndex($classes)
    {
		DSQL::call('WSComponentIndex', 'updateClassIndex', array($classes));
    }
}
?>