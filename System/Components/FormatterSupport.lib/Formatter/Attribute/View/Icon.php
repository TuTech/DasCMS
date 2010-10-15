<?php
/**
 * @deprecated use View_Content_* instead
 */
class Formatter_Attribute_View_Icon
    extends Formatter_Attribute_Options
    implements
        Interface_Formatter_Attribute_Linkable,
        Interface_Formatter_Attribute_OptionsSelectable
{
    protected $persistentAttributes = array('linkTarget','selectedOption');
    protected $linkTarget = null;
    protected $selectedOption = View_UIElement_Icon::SMALL;
    protected $options = array(
        View_UIElement_Icon::EXTRA_SMALL => 'extra-small',
        View_UIElement_Icon::SMALL => 'small',
        View_UIElement_Icon::MEDIUM => 'medium',
        View_UIElement_Icon::LARGE => 'large'
    );

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

    protected function getFormatterClass()
    {
        return 'Icon';
    }

    public function toXHTML($insertString = null)
    {
    	$insertString = strval($this->getContent()->getIcon()->asSize($this->selectedOption))."\n";
        if($this->isLinkingEnabled()){
    		$insertString = $this->createLink($insertString);
    	}
        return parent::toXHTML($insertString);
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