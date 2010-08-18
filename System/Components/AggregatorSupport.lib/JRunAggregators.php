<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2009-09-14
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage Job
 */
class JRunAggregators implements ISchedulerJob
{
    private $message = 'OK';
    private $code = 0;
    
    /**
     * offset for next run
     * @return int seconds
     */
    public function getInterval()
    {
        return ISchedulerJob::SECOND*20;
    }
    
    /**
     * @return void
     */
    public function run()
    {
        #echo 'aggregator';
        $c = Controller_Aggregators::getSharedInstance();
        $c->updateOutdatedAggregators();
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

	public function getEnd()
    {
        return null;
    }
}
?>