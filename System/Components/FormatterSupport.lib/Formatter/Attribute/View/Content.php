<?php
/**
 * @deprecated use View_Content_* instead
 */
class Formatter_Attribute_View_Content
    extends Formatter_Attribute_Text
{
    protected function getFormatterClass()
    {
        return 'Content';
    } 
    
    public function toXHTML($insertString = null)
    {
        try
        {
            return parent::toXHTML($this->getContent()->getContent());
        }
        catch (Exception $e)
        {
            return $e->getTraceAsString();
        }
    }
}
?>