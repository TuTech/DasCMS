<?php
/**
 * @deprecated use View_Content_* instead
 */
class Formatter_Attribute_View_Creator
    extends Formatter_Attribute_Info
    implements Interface_Formatter_Attribute_TextAppendable,
               Interface_Formatter_Attribute_TextPrependable
{
    protected function getFormatterClass()
    {
        return 'Creator';
    } 
    
    public function toXHTML($insertString = null)
    {
        return parent::toXHTML("\n<span class=\"user\">".$this->escapeString($this->getContent()->getCreatedBy())."</span>\n");
    }
}
?>