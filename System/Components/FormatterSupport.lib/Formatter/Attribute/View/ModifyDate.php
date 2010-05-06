<?php
class Formatter_Attribute_View_ModifyDate
    extends Formatter_Attribute_Date
{
    protected function getDate()
    {
        return $this->getContent()->getModifyDate();
    }
    
    protected function getFormatterClass()
    {
        return 'ModifyDate';
    }
}
?>