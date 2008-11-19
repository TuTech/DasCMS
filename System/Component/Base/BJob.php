<?php
/**
 * @package Bambus
 * @subpackage BaseClasses
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2008-11-18
 * @license GNU General Public License 3
 */
abstract class BJob extends BObject
{
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
    public function getEnd()
    {
        return null;
    }
}
?>