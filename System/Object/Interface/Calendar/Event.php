<?php
interface Interface_Calendar_Event extends Interface_Calendar_Entry
{
    public function __construct($startTime, $endTime, Interface_Content $content);
}
?>