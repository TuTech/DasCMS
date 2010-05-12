<?php
/**
 * @deprecated use View_Content_* instead
 */
class Formatter_Attribute_View_Title
    extends Formatter_Attribute_Linkable
{
    protected function getFormatterClass()
    {
        return 'Title';
    }

    public function toXHTML($insertString = null)
    {
        return parent::toXHTML('<h2>'.$this->escapeString($this->getContent()->getTitle())."</h2>\n");
    }
}
?>