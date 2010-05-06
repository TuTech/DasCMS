<?php
class Formatter_Attribute_View_Tags
    extends Formatter_Attribute_List
{
    protected function getFormatterClass()
    {
        return 'Tags';
    } 
    
    protected function getListEntries()
    {
        return $this->getContent()->getTags();
    }
}
?>