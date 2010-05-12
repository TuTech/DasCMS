<?php
/**
 * @deprecated
 */
interface Interface_Formatter_Attribute_HasLinkTarget
{
    public function getTargetView();
    public function getTargetFrame();
    
    public function setTargetView($viewName);
    public function setTargetFrame($frame);
}
?>