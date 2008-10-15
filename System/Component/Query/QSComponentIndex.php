<?php
class QSComponentIndex extends BQuery 
{
    public static function updateManagers($managers)
    {
		DSQL::call('WSComponentIndex', 'updateManagers', array($managers));
    }
}
?>