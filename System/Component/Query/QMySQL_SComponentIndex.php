<?php
class QMySQL_SComponentIndex extends BQuery 
{
    public static function updateManagers($managers)
    {
		parent::Database()->insert('Managers',array('manager'),$managers);
    }
}
?>