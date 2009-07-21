<?php
class hCalendar_Calendar extends _hCalendar implements Interface_Calendar_Calendar
{
    protected $entries = array();
    protected $title;
    public function __construct($title)
    {
        $this->title = $title;
        $this->sectionName = '';//vcalendar';
    }
    
    protected function getSectionBody()
    {
        $str = '';
        foreach ($this->entries as $entry)
        {
            $str .= strval($entry);
        }
        return $str;
    }
    
    public function addEntry(Interface_Calendar_Entry $entry)
    {
        $this->entries[] = $entry;
    }
    
    public function createEvent($startTime, $endTime, BContent $content)
    {
        return new hCalendar_Entry_Event($startTime, $endTime, $content);
    }
}
?>