<?php
class View_Content_Calendar_XHTML_Calendar 
    extends _View_Content_Calendar_XHTML 
    implements Interface_Calendar_Calendar
{
    protected $entries = array();
    protected $title;
    protected $formatterName = null;
    
    public function __construct($title)
    {
        $this->title = $title;
        $this->sectionName = 'Calendar';
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
    
    public function createEvent($startTime, $endTime, $alias)
    {
        return new View_Content_Calendar_XHTML_Entry_Event(
            $startTime, 
            $endTime, 
            Controller_Content::getSharedInstance()->accessContent($alias, $this, true),
            $this->formatterName
        );
    }
    
    public function setContentFormatter($formatterName)
    {
        $this->formatterName = $formatterName;
    }
}
?>