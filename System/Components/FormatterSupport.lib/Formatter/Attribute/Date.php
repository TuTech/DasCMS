<?php
/**
 * @deprecated use View_Content_* instead
 */
abstract class Formatter_Attribute_Date
    extends Formatter_Attribute_Info 
    implements 
        Interface_Formatter_Attribute_DateFormattable
{
    protected $dateFormat = 'r';
    protected $persistentAttributes = array('textAfter','textBefore','dateFormat');
    
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
        if($this->getDate() === null)
        {
            return '';
        }
        $str = sprintf(
        	"\n<span class=\"date\">%s</span>\n", 
            date($this->dateFormat, $this->getDate())
        );
        return parent::toXHTML($str);
    }
    
    public function toJSON(array $parentData = array())
    {
        if(!isset($parentData['data']))
        {
            $parentData['data'] = array();
        }
        $parentData['data']['dateFormat'] = $this->getDateFormat();
        return parent::toJSON($parentData);
    }
}
?>