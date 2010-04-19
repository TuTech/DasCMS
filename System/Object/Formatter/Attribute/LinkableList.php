<?php
abstract class Formatter_Attribute_LinkableList
    extends Formatter_Attribute_List
    implements Interface_Formatter_Attribute_Linkable
{
    protected $persistentAttributes = array('linkTarget','textAfter','textBefore','separator');
    protected $linkTarget = null;

    public function setLinkingTarget($linkTarget)
    {
        $this->linkTarget = $linkTarget;
    }

    public function getLinkingTarget()
    {
    	return $this->linkTarget;
    }

    public function isLinkingEnabled()
    {
        return $this->linkTarget != null;
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

	/**
     * @return VSpore
     */
    public function getTargetView()
    {
        return VSpore::byName($this->linkTarget);
    }
}
?>