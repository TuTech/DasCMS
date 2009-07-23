<?php
abstract class Formatter_Attribute_Date
    extends Formatter_Attribute_Info 
    implements 
        Interface_Formatter_Attribute_DateFormattable
{
    protected $dateFormat = 'c';

    abstract protected function getDate();
    
    public function getDateFormat()
    {
        return $this->dateFormat;
    }

    public function setDateFormat($format)
    {
        $this->dateFormat = strval($format);
    }
    
    public function toXHTML($insertString = null)
    {
        $str = sprintf(
        	"<span class=\"date\">%s</span>\n", 
            date(LConfiguration::get('dateformat'), $this->getDate())
        );
        return parent::toXHTML($str);
    }
}
?>