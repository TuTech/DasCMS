<?php
class Formatter_Attribute_View_Braker
    extends _Formatter_Attribute
{
    protected function getFormatterClass()
    {
        return 'Braker';
    } 
    
    public function toXHTML($insertString = null)
    {
        return sprintf("<br class=\"%s\"/>\n", $this->getFormatterClass());
    }
}
?>