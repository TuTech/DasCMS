<?php
class Formatter_Attribute_View_Location
    extends Formatter_Attribute_Info
{
    protected function getFormatterClass()
    {
        return 'Location';
    } 
    
    public function toXHTML($insertString = null)
    {
        return strval(new View_Content_Attribute_Location($this->getContent()));
    }
}
?>