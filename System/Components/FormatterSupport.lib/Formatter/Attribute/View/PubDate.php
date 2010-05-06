<?php
class Formatter_Attribute_View_PubDate
    extends Formatter_Attribute_Date
{
    protected function getDate()
    {
        return $this->getContent()->getPubDate();
    }
    
    protected function getFormatterClass()
    {
        return 'PubDate';
    }
}
?>