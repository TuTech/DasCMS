<?php
class Formatter_Attribute_View_Icon
    extends Formatter_Attribute_Options 
    implements 
        Interface_Formatter_Attribute_Linkable,
        Interface_Formatter_Attribute_OptionsSelectable
{
    protected $enableLinking = false;
    protected $selectedOption = WIcon::SMALL;
    protected $options = array(
        WIcon::EXTRA_SMALL => 'extra-small',
        WIcon::SMALL => 'small',
        WIcon::MEDIUM => 'medium',
        WIcon::LARGE => 'large'
    );
    
    public function setLinkingEnabled($enabled)
    {
        $this->enableLinking = $enabled == true;
    }

    public function isLinkingEnabled()
    {
        return $this->enableLinking;
    }
    
    protected function getFormatterClass()
    {
        return 'Icon';
    } 
    
    public function toXHTML($insertString = null)
    {
        return parent::toXHTML(strval($this->getContent()->getIcon()->asSize($this->selectedOption)));
    }
}
?>