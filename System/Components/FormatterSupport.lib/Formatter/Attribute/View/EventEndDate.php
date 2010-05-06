<?php
class Formatter_Attribute_View_EventEndDate
    extends Formatter_Attribute_Date
{
    protected function getDate()
    {
        $c = $this->getContent();
        return $c->hasComposite('EventDates') ? $c->getEventEndDate() : null;
    }
    
    protected function getFormatterClass()
    {
        return 'EventEndDate';
    }
}
?>