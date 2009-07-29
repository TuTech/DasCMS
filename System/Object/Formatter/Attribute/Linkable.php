<?php
abstract class Formatter_Attribute_Linkable
    extends _Formatter_Attribute 
    implements Interface_Formatter_Attribute_Linkable
{
    protected $persistentAttributes = array('enableLinking');
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
    
    public function toJSON(array $parentData = array())
    {
        if(!isset($parentData['data']))
        {
            $parentData['data'] = array();
        }
        $parentData['data']['linkingEnabled'] = $this->isLinkingEnabled();
        return parent::toJSON($parentData);
    }
}
?>