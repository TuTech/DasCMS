<?php
abstract class Formatter_Attribute_LinkableList
    extends Formatter_Attribute_List
    implements Interface_Formatter_Attribute_Linkable
{
    protected $persistentAttributes = array('enableLinking','textAfter','textBefore','separator');
    protected $enableLinking = false;

    public function setLinkingEnabled($enabled)
    {
        $this->enableLinking = $enabled == true;
    }

    public function isLinkingEnabled()
    {
        return $this->enableLinking;
    }      

    abstract function getLinkListAliases();
    
    public function toXHTML()
    {
        //FIXME
    }
    
    
    public function toJSON(array $parentData = array())
    {
        //FIXME
    }
}
?>