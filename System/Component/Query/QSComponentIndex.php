<?php
class QSComponentIndex extends BQuery 
{
    public static function updateClassIndex($classes)
    {
		DSQL::call('SComponentIndex', 'updateClassIndex', array($classes));
    }
}
?>