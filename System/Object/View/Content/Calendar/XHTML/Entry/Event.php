<?php
class View_Content_Calendar_XHTML_Entry_Event 
    extends _View_Content_Calendar_XHTML_Entry 
    implements Interface_Calendar_Event
{
    protected $startTime, $endTime;
    /**
     * @var BContent
     */
    protected $content;
    private $formatterName = null;
    
    public function __construct($startTime, $endTime, BContent $content, $formatterName = null)
    {
        $this->sectionName = 'vevent';
        $this->startTime = $startTime;
        $this->endTime = $endTime;
        $this->content = $content;
        $this->formatterName = $formatterName;
    }
    
    protected function getSectionBody()
    {
        $str = '';
        if($this->formatterName != null)
        {
            $str = Formatter_Container::unfreezeForFormatting($this->formatterName, $this->content);
        }
        if($str == '')//formatter failed orwas not set
        {
            //hCal microformat
            $str.= $this->withTag('h3')->makeCommand('SUMMARY',$this->content->getTitle());
            $str.= $this->withTag('div')->makeDateCommand('DTSTART' ,$this->startTime);
            $str.= $this->withTag('div')->makeDateCommand('DTEND',  $this->endTime);
            $str.= $this->withTag('span')->makeCommand('UID',$this->content->getGUID());
            $str.= $this->withTag('div')->makeCommand('DESCRIPTION',strip_tags($this->content->getDescription()));
            $str.= $this->withTag('div')->makeDateCommand('CREATED',$this->content->getCreateDate());
        }
        return $str;
    }
}
?>