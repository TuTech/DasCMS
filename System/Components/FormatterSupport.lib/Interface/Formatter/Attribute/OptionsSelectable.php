<?php
interface Interface_Formatter_Attribute_OptionsSelectable
{
    public function getAvailableOptions();
    
    public function getSelectedOption();
        
    public function setSelectedOption($option);
}
?>