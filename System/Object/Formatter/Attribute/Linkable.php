<?php
abstract class Formatter_Attribute_Linkable
    extends _Formatter_Attribute 
    implements Interface_Formatter_Attribute_Linkable
{
    protected $enableLinking = false;

    public function setLinkingEnabled($enabled)
    {
        $this->enableLinking = $enabled == true;
    }

    public function isLinkingEnabled()
    {
        return $this->enableLinking;
    }
    
    abstract function getLinkAlias();
    
    public function toXHTML($insertString = null)
    {
        return parent::toXHTML($insertString);
    }
}
?>