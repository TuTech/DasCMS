<?php
class View_hCalendar_Entry_Event extends _View_hCalendar_Entry implements Interface_Calendar_Event
{
    protected $startTime, $endTime;
    /**
     * @var BContent
     */
    protected $content;
    
    public function __construct($startTime, $endTime, BContent $content)
    {
        $this->sectionName = 'vevent';
        $this->tag = 'div';
        $this->startTime = $startTime;
        $this->endTime = $endTime;
        $this->content = $content;
    }
    
    protected function getSectionBody()
    {
        $str = '';
        $str.= $this->makeCommand('h3','SUMMARY',$this->content->getTitle());
        $str .= $this->makeDateCommand('DTSTART' ,$this->startTime);
        $str.= $this->makeDateCommand('DTEND',  $this->endTime);
        $str.= $this->makeCommand('span','UID',$this->content->getGUID());
        $str.= $this->makeCommand('div','DESCRIPTION',strip_tags($this->content->getDescription()));
        $str.= $this->makeDateCommand('CREATED',$this->content->getCreateDate());
        $str.= new View_Content_Attribute_Location($this->content);
        return $str;
    }
}
?>