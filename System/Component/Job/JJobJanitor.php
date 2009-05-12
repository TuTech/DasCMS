<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2008-11-19
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage Job
 */
class JJobJanitor extends BJob 
{
    private $message = 'OK';
    private $code = 0;
    
    /**
     * offset for next run
     * @return int seconds
     */
    public function getInterval()
    {
        return BJob::DAY;
    }
    
    /**
     * @return void
     */
    public function run()
    {
        $jobs = array();
        $toAdd = array();
        $toRemove = array();
        
        //get all indexed job classes
        $SCI = SComponentIndex::getSharedInstance();
        $indexed = $SCI->ExtensionsOf('BJob');

        //get all registered jobs
        $res = QJJobJanitor::getJobList();
        while($row = $res->fetch())
        {
            list($id, $class, $start, $stop) = $row;
            $jobs[$class] = $id;
            if(!in_array($class, $indexed))
            {
                $toRemove[] = $id;
            }
        }
        //remove jobs that are not indexed
        QJJobJanitor::removeJobs($toRemove);
        
        //find jobs that are not registered
        foreach ($indexed as $class) 
        {
        	if(!array_key_exists($class, $jobs))
        	{
        	    $toAdd[] = $class;
        	}
        }
        //add them if they are valid jobs
        $exc = 0;
        foreach ($toAdd as $class) 
        {
        	if(class_exists($class, true))
        	{
        	    try{
        	        $o = new $class();
    	            if ($o instanceof BJob) 
    	            {
    	            	$int = $o->getInterval();
    	            	$end = $o->getEnd();
    	            	QJJobJanitor::addNewJob($class, $int, $end);
    	            	QJJobJanitor::scheduleNewJob($class);
    	            }
    	            unset($o);
        	    }
        	    catch (Exception $e){/*ignore this and continue*/ $exc++;
        	        echo $e->getMessage(), "\n<br />";
        	        echo $e->getFile(), "\n<br />";
        	        echo $e->getLine(), "\n<br />";
        	        echo $e->getTraceAsString(), "\n<br />";
        	    }
        	}
        }
        if($exc)
        {
            $this->message = $exc.' jobs not indexed';
            $this->code = 1;
        }
    }
    
    /**
     * get status text for the processed result
     * @return string (max length 64)
     */
    public function getStatusMessage()
    {
        return $this->message;
    }
    
    /**
     * get status code
     * @return int status const
     */
    public function getStatusCode()
    {
        return $this->code;
    }
}
?>