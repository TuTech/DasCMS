<?php
class QMySQL_SComponentIndex extends BQuery 
{
    /**
     * @return void
     */
    public static function updateClassIndex($classes)
    {
        $DB = BQuery::Database();
        $ci = array();
        foreach ($classes as $cname => $cguid) 
        {
            $cn = '"'.$DB->escape($cname).'"';
            $cg = empty($cguid) ? 'NULL' : '"'.$DB->escape($cguid).'"';
        	$ci[] = array($cn, $cg);
        }
		$DB->insertUnescaped('Classes',array('class', 'guid'),$ci, true);
    }
}
?>