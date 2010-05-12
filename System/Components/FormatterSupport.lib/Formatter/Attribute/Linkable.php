<?php
/**
 * @deprecated use View_Content_* instead
 */
abstract class Formatter_Attribute_Linkable
    extends _Formatter_Attribute
    implements Interface_Formatter_Attribute_Linkable
{
    protected $persistentAttributes = array('linkTarget');
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

    public function toXHTML($insertString = null)
    {
        if($this->isLinkingEnabled()){
    		$insertString = $this->createLink($insertString);
    	}
        return parent::toXHTML($insertString);
    }

    public function toJSON(array $parentData = array())
    {
        if(!isset($parentData['data']))
        {
            $parentData['data'] = array();
        }
        $parentData['data']['linkTarget'] = $this->getLinkingTarget();
        return parent::toJSON($parentData);
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