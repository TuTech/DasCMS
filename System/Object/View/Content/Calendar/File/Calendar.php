<?php
class View_Content_Calendar_File_Calendar
    extends _View_Content_Calendar_File
    implements Interface_Calendar_Calendar
{
    protected $entries = array();
    protected $title;
    
    public function __construct($title)
    {
        $this->title = $title;
        $this->sectionName = 'VCALENDAR';
    }
    
    protected function getSectionBody()
    {
        $str = $this->makeCommand('VERSION', '2.0');
        $str.= $this->makeCommand('PRODID', '-//Capricore/Calendar//NONSGML v1.0//EN');
        $str.= $this->makeCommand('X-WR-CALNAME', $this->title, true);
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
    
    public function createEvent($startTime, $endTime, $alias)
    {
        return new View_Content_Calendar_File_Entry_Event($startTime, $endTime, BContent::Access($alias, $this, true));
    }
}
?>