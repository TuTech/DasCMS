<?php
/**
 *
 * @author lse
 */
interface ISchedulerJob {
    const SECOND = 1;
    const MINUTE = 60;
    const HOUR = 3600;
    const DAY = 86400;
    const WEEK = 604800;

	/**
     * offset for next run
     * @return int seconds
     */
	abstract public function getInterval();
    /**
     * @return void
     */
    abstract public function run();
    /**
     * get status text for the processed result
     * @return string (max length 64)
     */
    abstract public function getStatusMessage();
    /**
     * get status code
     * @return int status const
     */
    abstract public function getStatusCode();

    /**
     * return time to stop this or null
     *
     * @return int|null
     */
    public function getEnd();
}
?>
