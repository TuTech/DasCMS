<?php
class Formatter_Attribute_View_Content
    extends Formatter_Attribute_Text
{
    protected function getFormatterClass()
    {
        return 'Content';
    } 
    
    public function toXHTML($insertString = null)
    {
        return parent::toXHTML($this->getContent()->getContent());
    }
}
?>