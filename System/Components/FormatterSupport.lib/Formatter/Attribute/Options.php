<?php
/**
 * @deprecated use View_Content_* instead
 */
abstract class Formatter_Attribute_Options
    extends _Formatter_Attribute
    implements Interface_Formatter_Attribute_OptionsSelectable
{
    protected $persistentAttributes = array('selectedOption');
    protected $options = array();
    protected $selectedOption = null;

    public function getAvailableOptions()
    {
        return $this->options;
    }

    public function getSelectedOption()
    {
        return $this->selectedOption;
    }

    public function setSelectedOption($option)
    {
        if(array_key_exists($option, $this->getAvailableOptions()))
        {
            $this->selectedOption = $option;
        }
        else
        {
            throw new XUndefinedIndexException('given option not available');
        }
    }

    public function toJSON(array $parentData = array())
    {
        if(!isset($parentData['data']))
        {
            $parentData['data'] = array();
        }
        $parentData['data']['options'] = $this->getAvailableOptions();
        $parentData['data']['selectedOption'] = $this->getSelectedOption();
        return parent::toJSON($parentData);
    }

	public function toXHTML($insertString = null)
    {
        return parent::toXHTML($insertString);
    }
}
?>