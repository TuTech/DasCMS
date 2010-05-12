<?php
/**
 * @deprecated use View_Content_* instead
 */
class Formatter_Attribute_View_Subtitle
    extends Formatter_Attribute_Linkable
{
    protected function getFormatterClass()
    {
        return 'Subtitle';
    } 
    
    public function toXHTML($insertString = null)
    {
        return parent::toXHTML('<h3>'.$this->escapeString($this->getContent()->getSubTitle())."</h3>\n");
    }
    
    public function getLinkAlias()
    {
        return $this->getContent()->getAlias();
    }
}
?>