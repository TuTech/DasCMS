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
class Task_TruncateTaskLog implements Interface_SchedulerTask
{
    private $message = 'OK';
    private $code = 0;
    
    /**
     * offset for next run
     * @return int seconds
     */
    public function getInterval()
    {
        return Interface_SchedulerTask::DAY;
    }
    
    /**
     * @return void
     */
    public function run()
    {
        $this->message = sprintf(
				'%d job schedules removed',
				Core::Database()
					->createQueryForClass($this)
					->call('cleanup')
					->withoutParameters()
					->execute()
			);
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