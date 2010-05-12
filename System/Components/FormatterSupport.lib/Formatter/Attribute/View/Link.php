<?php
/**
 * @deprecated use View_Content_* instead
 */
class Formatter_Attribute_View_Link
    extends Formatter_Attribute_Link
{
    public function getLinkAlias()
    {
        return $this->getContent()->getAlias();
    }
    
    protected function getFormatterClass()
    {
        return 'Link';
    } 
    
    public function setTargetFrame($frame){}//FIXME
    public function setTargetView($viewName){}//FIXME
}
?>