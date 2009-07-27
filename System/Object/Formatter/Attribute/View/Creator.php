<?php
class Formatter_Attribute_View_Creator
    extends Formatter_Attribute_Linkable
    implements Interface_Formatter_Attribute_Linkable
{
    protected function getFormatterClass()
    {
        return 'Creator';
    } 
    
    public function toXHTML($insertString = null)
    {
        return parent::toXHTML($this->escapeString($this->getContent()->getCreatedBy()));
    }
    
    public function getLinkAlias()
    {
        return $this->getContent()->getAlias();
    }
}
?>