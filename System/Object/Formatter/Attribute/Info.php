<?php
abstract class Formatter_Attribute_Info
    extends _Formatter_Attribute 
    implements 
        Interface_Formatter_Attribute_TextPrependable,
        Interface_Formatter_Attribute_TextAppendable
{
    protected $textBefore = '';
    protected $textAfter = '';
    protected $persistentAttributes = array('textAfter','textBefore');
    
    //Interface_Formatter_Attribute_TextPrependable
    public function getPrependedText()
    {
        return $this->textBefore;
    }

    public function setPrependedText($text)
    {
        $this->textBefore = strval($text);
    }

    //Interface_Formatter_Attribute_TextAppendable
    public function getAppendedText()
    {
        return $this->textAfter;
    }
    
    public function setAppendedText($text)
    {
        $this->textAfter = strval($text);
    }
    
    public function toXHTML($insertString = null)
    {
        $insertString = $insertString == null ? '' : strval($insertString); 
        $str = sprintf(
        	"<span class=\"textBefore\">%s</span>%s<span class=\"textAfter\">%s</span>\n"
        	,htmlentities($this->textBefore, ENT_QUOTES, CHARSET)
        	,$insertString
        	,htmlentities($this->textAfter, ENT_QUOTES, CHARSET)
    	);
    	return parent::toXHTML($str);
    }
    
    public function toJSON(array $parentData = array())
    {
        if(!isset($parentData['data']))
        {
            $parentData['data'] = array();
        }
        $parentData['data']['prependedText'] = $this->getPrependedText();
        $parentData['data']['appendedText'] = $this->getAppendedText();
        return parent::toJSON($parentData);
    }
}
?>