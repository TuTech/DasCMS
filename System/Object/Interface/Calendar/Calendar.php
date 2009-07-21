<?php
interface Interface_Calendar_Calendar
{
    public function __construct($title);
    public function addEntry(Interface_Calendar_Entry $entry);
    /**
     * 
     * @param int $startTime
     * @param int $endTime
     * @param BContent $content
     * @return Interface_Calendar_Event
     */
    public function createEvent($startTime, $endTime, BContent $content);
}
?>