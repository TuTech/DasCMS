<?php
abstract class Formatter_Attribute_LinkableList
    extends Formatter_Attribute_List
    implements Interface_Formatter_Attribute_Linkable
{
    protected $link = false;

    public function setLinkingEnabled($enabled)
    {
        $this->link = $enabled == true;
    }

    public function isLinkingEnabled()
    {
        return $this->link;
    }        
}
?>