<?php
abstract class Formatter_Attribute_Link 
    extends _Formatter_Attribute 
    implements 
        Interface_Formatter_Attribute_TextSettable,
        Interface_Formatter_Attribute_HasLinkTarget        
{
    protected $persistentAttributes = array('text');
    protected $text = '';
        
    public function setText($text)
    {
        $this->text = strval($text);
    }

    public function getText()
    {
        return $this->text;
    }
    
    abstract function getLinkAlias();
        
    public function toXHTML($insertString = null)
    {
        $insertString == null ? htmlentities($this->getText(), ENT_QUOTES, CHARSET) : $insertString;
        $str = $this->createLink($this->getLinkAlias(),$insertString);
        return parent::toXHTML($str);
    }
}
?>