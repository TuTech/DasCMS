<?php
class Formatter_Attribute_View_EventStartDate
    extends Formatter_Attribute_Date
{
    protected function getDate()
    {
        $c = $this->getContent();
        return $c->hasComposite('EventDates') ? $c->getEventStartDate() : null;
    }
    
    protected function getFormatterClass()
    {
        return 'EventStartDate';
    }
}
?>