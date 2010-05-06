<?php
class Formatter_Attribute_View_Description
    extends Formatter_Attribute_Text
{
    protected function getFormatterClass()
    {
        return 'Description';
    } 
    
    public function toXHTML($insertString = null)
    {
        return parent::toXHTML($this->getContent()->getDescription());
    }
}
?>