<?php
class QMySQL_SComponentIndex extends BQuery 
{
    /**
     * @return void
     */
    public static function updateClassIndex($classes)
    {
        $ci = array();
        foreach ($classes as $cname => $cguid) 
        {
        	$ci[] = array($cname, $cguid);
        }
		parent::Database()->insert('Classes',array('class', 'guid'),$ci, true);
    }
}
?>